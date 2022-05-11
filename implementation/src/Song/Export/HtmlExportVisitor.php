<?php
/**
 * @author Jakub Jabůrek <jaburek.jakub@gmail.com>
 */

declare(strict_types=1);

namespace Chords\Song\Export;

use BadMethodCallException;
use RuntimeException;
use UnexpectedValueException;
use Chords\Song\Model\Chord;
use Chords\Song\Model\Paragraph;
use Chords\Song\Model\Repeat;
use Chords\Song\Model\Song;
use Chords\Song\Model\SongInfo;
use Chords\Song\Model\SongLyrics;
use Chords\Song\Model\Strophe;
use Chords\Song\Model\StropheReference;
use Chords\Song\Model\Text;
use Chords\Song\Model\Verse;

/**
 * Transforms a song into HTML code.
 *
 * @package Chords\Song\Export
 */
final class HtmlExportVisitor implements VisitorInterface {
	/**
	 * HTML class of a chord element.
	 *
	 * @var string
	 */
	private const CHORD_CLASSNAME = 'chord';

	/**
	 * HTML class of a paragraph.
	 *
	 * @var string
	 */
	private const PARAGRAPH_CLASSNAME = 'song-paragraph';

	/**
	 * HTML class of a repeat marker.
	 *
	 * @var string
	 */
	private const REPEAT_CLASSNAME = 'repeat-marker';

	/**
	 * Repeat marker start.
	 *
	 * @var string
	 */
	private const REPEAT_START = '[: ';

	/**
	 * Repeat marker end.
	 *
	 * @var string
	 */
	private const REPEAT_END = ' :]';

	/**
	 * Repeat count formatting string.
	 *
	 * @var string
	 */
	private const REPEAT_COUNT_FORMAT = '%d×';

	/**
	 * Default repeat count.
	 *
	 * @var int
	 */
	private const REPEAT_COUNT_DEFAULT = 2;

	/**
	 * HTML class of strophe label.
	 *
	 * @var string
	 */
	private const STROPHE_LABEL_CLASSNAME = 'strophe-label';

	/**
	 * Strophe label formatting string.
	 *
	 * @var string
	 */
	private const STROPHE_LABEL_FORMAT = '%s ';

	/**
	 * Strophe label with repeat indicator formatting string.
	 *
	 * @var string
	 */
	private const STROPHE_LABEL_REPEAT_FORMAT = '%s (%d×) ';

	/**
	 * Strophe reference formatting string.
	 *
	 * @var string
	 */
	private const STROPHE_REFERENCE_FORMAT = '%s';

	/**
	 * Strophe reference with repeat indicator formatting string.
	 *
	 * @var string
	 */
	private const STROPHE_REFERENCE_REPEAT_FORMAT = '%s %d×';

	/**
	 * HTML class of strophe reference.
	 *
	 * @var string
	 */
	private const STROPHE_REFERENCE_CLASSNAME = 'strophe-reference';

	/**
	 * Lines of HTML code.
	 *
	 * @var string[]
	 */
	private $html = [];

	/**
	 * Chunks in the current line.
	 *
	 * Contains arrays with `chord` and `text` keys.
	 *
	 * @var array[]|null
	 */
	private $row = null;

	/**
	 * Repeat count of the currently processed node.
	 *
	 * @var int|null
	 */
	private $repeat = null;

	/**
	 * Label of the currently processed strophe.
	 *
	 * @var string|null
	 */
	private $label = null;

	/**
	 * Returns the built HTML.
	 *
	 * @return string HTML
	 */
	public function saveHtml(): string {
		return implode('', $this->html);
	}

	/**
	 * @inheritdoc
	 */
	public function visitChord(Chord $chord): void {
		if (!$this->row) {
			throw new BadMethodCallException('Cannot visit Chord while not in a Verse context.');
		}

		$this->row['chords'][] = $chord->getName();
		$this->row['text'][] = null;
	}

	/**
	 * @inheritdoc
	 */
	public function visitParagraph(Paragraph $paragraph): void {
		$this->html[] = sprintf('<div class="%s">', htmlspecialchars(self::PARAGRAPH_CLASSNAME));

		foreach ($paragraph->getNodes() as $node) {
			$node->accept($this);
		}

		$this->html[] = '</div>';
	}

	/**
	 * @inheritdoc
	 */
	public function visitRepeat(Repeat $repeat): void {
		if ($this->row) {
			$this->row['chords'][] = null;
			$this->row['text'][] = [
				'text' => self::REPEAT_START,
				'className' => self::REPEAT_CLASSNAME
			];

			foreach ($repeat->getNodes() as $node) {
				$node->accept($this);
			}

			$end = self::REPEAT_END;

			if ($repeat->getCount() !== self::REPEAT_COUNT_DEFAULT) {
				$end .= ' '.sprintf(self::REPEAT_COUNT_FORMAT, $repeat->getCount());
			}

			$this->row['chords'][] = null;
			$this->row['text'][] = [
				'text' => $end,
				'className' => self::REPEAT_CLASSNAME
			];
		} else {
			$this->repeat = $repeat->getCount();

			if (count($repeat->getNodes()) !== 1 || (
			    	!$repeat->getNodes()[0] instanceof Strophe &&
			    	!$repeat->getNodes()[0] instanceof StropheReference &&
			    	!$repeat->getNodes()[0] instanceof Verse
			    )) {
				throw new UnexpectedValueException('Only single strophe, strophe reference or verse is supported when repeating blocks.');
			}

			foreach ($repeat->getNodes() as $node) {
				$node->accept($this);
			}

			$this->repeat = null;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function visitSong(Song $song): void {
		$song->getLyrics()->accept($this);
	}

	/**
	 * @inheritdoc
	 */
	public function visitSongInfo(SongInfo $info): void {
		throw new RuntimeException('Not supported.');
	}

	/**
	 * @inheritdoc
	 */
	public function visitSongLyrics(SongLyrics $lyrics): void {
		foreach ($lyrics->getNodes() as $node) {
			$node->accept($this);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function visitStrophe(Strophe $strophe): void {
		$this->label = $strophe->getLabel();

		foreach ($strophe->getNodes() as $node) {
			$node->accept($this);
		}

		$this->label = null;
	}

	/**
	 * @inheritdoc
	 */
	public function visitStropheReference(StropheReference $reference): void {
		if ($reference->getStrophe()->getLabel() === null) {
			throw new UnexpectedValueException('Cannot output a strophe reference element pointing to an unlabeled strophe.');
		}

		if ($this->repeat !== null) {
			$text = sprintf(self::STROPHE_REFERENCE_REPEAT_FORMAT, $reference->getStrophe()->getLabel(), $this->repeat);

			$this->repeat = null;
		} else {
			$text = sprintf(self::STROPHE_REFERENCE_FORMAT, $reference->getStrophe()->getLabel());
		}

		$this->html[] = sprintf(
			'<div class="%s"><table><tbody><tr><td class="%s"></td></tr><tr><td class="%s">%s</td></tr></tbody></table></div>',
			self::PARAGRAPH_CLASSNAME,
			self::CHORD_CLASSNAME,
			self::STROPHE_REFERENCE_CLASSNAME,
			htmlspecialchars($text)
		);
	}

	/**
	 * @inheritdoc
	 */
	public function visitVerse(Verse $verse): void {
		$this->row = [
			'chords' => [],
			'text' => []
		];

		$nodes = $verse->getNodes();

		if ($this->label !== null) {
			if ($this->repeat !== null) {
				$formattedLabel = sprintf(self::STROPHE_LABEL_REPEAT_FORMAT, $this->label, $this->repeat);
			} else {
				$formattedLabel = sprintf(self::STROPHE_LABEL_FORMAT, $this->label);
			}

			$this->row['chords'][] = null;
			$this->row['text'][] = [
				'text' => $formattedLabel,
				'className' => self::STROPHE_LABEL_CLASSNAME
			];

			$this->label = $this->repeat = null;
		} else if ($this->repeat !== null) {
			$nodes = [new Repeat($verse->getNodes(), $this->repeat)];
		}

		foreach ($nodes as $node) {
			$node->accept($this);
		}

		$count = count($this->row['chords']);
		$chords = '<tr>';
		$text = '<tr>';

		for ($i = 0; $i < $count; $i++) {
			$chords .= sprintf(
				'<td class="%s">%s</td>',
				htmlspecialchars(self::CHORD_CLASSNAME),
				htmlspecialchars($this->row['chords'][$i] ?? '')
			);

			if ($this->row['text'][$i] === null) {
				$this->row['text'][$i] = [
					'text' => null
				];
			}

			$attributes = [];

			if (isset($this->row['text'][$i]['className'])) {
				$attributes[] = [
					'class',
					$this->row['text'][$i]['className']
				];
			}

			$attributes = array_reduce($attributes, function ($carry, $item) {
				return sprintf(
					'%s %s="%s"',
					$carry ?? '',
					$item[0],
					htmlspecialchars($item[1])
				);
			}) ?? '';

			// force hard spaces at the beginning and end of <td> element,
			// otherwise they would get ignored by the browser
			$cellText = $this->row['text'][$i]['text'] ?? '';
			$cellText = preg_replace('/^ /', ' ', $cellText);
			$cellText = preg_replace('/ $/', ' ', $cellText);

			$text .= sprintf(
				'<td%s>%s</td>',
				$attributes,
				htmlspecialchars($cellText)
			);
		}

		$chords .= '</tr>';
		$text .= '</tr>';

		$this->html[] = sprintf(
			'<table><tbody>%s%s</tbody></table>',
			$chords,
			$text
		);

		$this->row = null;
	}

	/**
	 * @inheritdoc
	 */
	public function visitText(Text $text): void {
		if (!$this->row) {
			throw new BadMethodCallException('Cannot visit Text while not in a Verse context.');
		}

		$count = count($this->row['text']);

		if ($count > 0 && $this->row['text'][$count - 1] === null) {
			$this->row['text'][$count - 1] = [
				'text' => $text->getText()
			];
		} else {
			$this->row['text'][] = [
				'text' => $text->getText()
			];

			$this->row['chords'][] = null;
		}
	}
}