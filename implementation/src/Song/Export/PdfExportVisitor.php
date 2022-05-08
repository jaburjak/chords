<?php
declare(strict_types=1);

namespace Chords\Song\Export;

use \BadMethodCallException;
use \RuntimeException;
use \UnexpectedValueException;
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

final class PdfExportVisitor implements VisitorInterface {
	private const HEADER_CLASSNAME = 'header';

	private const AUTHOR_CLASSNAME = 'author';

	private const METADATA_CLASSNAME = 'meta';

	private const CELL_CLASSNAME = 'cell';

	private const TEXT_CLASSNAME = 'text';

	private const VERSE_CLASSNAME = 'verse';

	private const CHORD_CLASSNAME = 'chord';

	private const PARAGRAPH_CLASSNAME = 'paragraph';

	private const REPEAT_CLASSNAME = 'repeat-marker';

	private const REPEAT_START = '[: ';

	private const REPEAT_END = ' :]';

	private const REPEAT_COUNT_FORMAT = '%d×';

	private const REPEAT_COUNT_DEFAULT = 2;

	private const STROPHE_LABEL_CLASSNAME = 'strophe-label';

	private const STROPHE_LABEL_FORMAT = '%s ';

	private const STROPHE_LABEL_REPEAT_FORMAT = '%s (%d×) ';

	private const STROPHE_REFERENCE_FORMAT = '%s';

	private const STROPHE_REFERENCE_REPEAT_FORMAT = '%s %d×';

	private const STROPHE_REFERENCE_CLASSNAME = 'strophe-reference';

	/**
	 * @var PdfExportOptions
	 */
	private $options;

	/**
	 * @var string[]
	 */
	private $html = [];

	/**
	 * @var array|null
	 */
	private $row = null;

	/**
	 * @var int|null
	 */
	private $repeat = null;

	/**
	 * @var string|null
	 */
	private $label = null;

	/**
	 * @var bool
	 */
	private $hasChords = false;

	public function __construct(PdfExportOptions $options) {
		$this->options = $options;
	}

	public function saveHtml(): string {
		return implode('', $this->html);
	}

	/**
	 * @inheritdoc
	 */
	public function visitChord(Chord $chord): void {
		if ($this->row === null) {
			throw new BadMethodCallException('Cannot visit Chord while not in a Verse context.');
		}

		$this->row[] = [
			'chord' => $chord->getName(),
			'text' => ''
		];

		if ($chord->isPrint() || $this->options->isPrintHiddenChords()) {
			$this->hasChords = true;
		}
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
		if ($this->row !== null) {
			$this->row[] = [
				'chord' => '',
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

			$this->row[] = [
				'chord' => '',
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
		$song->getInfo()->accept($this);
		$song->getLyrics()->accept($this);
	}

	/**
	 * @inheritdoc
	 */
	public function visitSongInfo(SongInfo $info): void {
		$this->html[] = sprintf('<div class="%s">', htmlspecialchars(self::HEADER_CLASSNAME));
		$this->html[] = sprintf('<h1>%s</h1>', htmlspecialchars($info->getTitle()));

		if ($info->getAuthor() === null) {
			$author = [];
		} else {
			$author = [$info->getAuthor()];
		}

		if (count($this->options->getMetadata()) > 0 && $this->options->getMetadata()[0] !== '') {
			$author[] = $this->options->getMetadata()[0];
		}

		if ($info->getAuthor() !== null) {
			$this->html[] = sprintf(
				'<div class="%s">%s</div>',
				htmlspecialchars_decode(self::AUTHOR_CLASSNAME),
				htmlspecialchars(implode(' ', $author))
			);
		}

		for ($i = 1; $i < count($this->options->getMetadata()); $i++) {
			$this->html[] = sprintf(
				'<div class="%s">%s</div>',
				htmlspecialchars_decode(self::METADATA_CLASSNAME),
				htmlspecialchars($this->options->getMetadata()[$i])
			);
		}

		$this->html[] = '</div>';
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
			'<div class="%s"><div class="%s"><span class="%s"><span class="%s"></span><br><span class="%s %s">%s</span></span></div></div>',
			htmlspecialchars(self::PARAGRAPH_CLASSNAME),
			htmlspecialchars(self::VERSE_CLASSNAME),
			htmlspecialchars(self::CELL_CLASSNAME),
			htmlspecialchars(self::CHORD_CLASSNAME),
			htmlspecialchars(self::TEXT_CLASSNAME),
			htmlspecialchars(self::STROPHE_REFERENCE_CLASSNAME),
			htmlspecialchars($text)
		);
	}

	/**
	 * @inheritdoc
	 */
	public function visitVerse(Verse $verse): void {
		$this->row = [];
		$this->hasChords = false;

		$nodes = $verse->getNodes();

		if ($this->label !== null) {
			if ($this->repeat !== null) {
				$formattedLabel = sprintf(self::STROPHE_LABEL_REPEAT_FORMAT, $this->label, $this->repeat);
			} else {
				$formattedLabel = sprintf(self::STROPHE_LABEL_FORMAT, $this->label);
			}

			$this->row[] = [
				'chord' => '',
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

		$verseHtml = '';

		foreach ($this->row as $cell) {
			// force hard spaces at the beginning and end of an element,
			// otherwise they would get ignored
			$text = preg_replace('/^ /', ' ', $cell['text']);
			$text = preg_replace('/ $/', ' ', $text);

			$class = self::CELL_CLASSNAME;

			if (isset($cell['className'])) {
				$class .= ' '.$cell['className'];
			}

			$verseHtml .= sprintf('<div class="%s">', htmlspecialchars($class));

			if ($this->hasChords) {
				$verseHtml .= sprintf('<span class="%s">%s</span>', htmlspecialchars(self::CHORD_CLASSNAME), htmlspecialchars($cell['chord']));
				$verseHtml .= '<br>';

				if ($cell['chord'] !== '' && $text === '') {
					$text = ' ';
				}
			}

			$verseHtml .= sprintf('<span class="%s">%s</span>', htmlspecialchars(self::TEXT_CLASSNAME), htmlspecialchars($text));
			$verseHtml .= '</div>';
		}

		$this->html[] = sprintf('<div class="%s">%s</div>', htmlspecialchars(self::VERSE_CLASSNAME), $verseHtml);

		$this->row = null;
		$this->hasChords = false;
	}

	/**
	 * @inheritdoc
	 */
	public function visitText(Text $text): void {
		if ($this->row === null) {
			throw new BadMethodCallException('Cannot visit Text while not in a Verse context.');
		}

		$chunks = explode(' ', $text->getText());
		$chunkCount = count($chunks);

		foreach ($chunks as $i => $chunk) {
			$count = count($this->row);

			if ($i + 1 < $chunkCount) {
				$chunk .= ' ';
			}

			if ($chunk === '') {
				continue;
			}

			if ($count > 0 && $this->row[$count - 1]['text'] === '') {
				$this->row[$count - 1]['text'] = $chunk;
			} else {
				$this->row[] = [
					'chord' => '',
					'text' => $chunk
				];
			}
		}
	}
}