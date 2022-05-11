<?php
/**
 * @author Jakub Jabůrek <jaburek.jakub@gmail.com>
 */

declare(strict_types=1);

namespace Chords\Song\Export;

use DOMDocument;
use DOMImplementation;
use SplStack;
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
 * Saves a song as a Song 1.0 XML document.
 *
 * @package Chords\Song\Export
 */
final class XmlExportVisitor implements VisitorInterface {
	/**
	 * Version of the XML (not Song) specification.
	 *
	 * @var string
	 */
	private const XML_VERSION = '1.0';

	/**
	 * Document encoding.
	 *
	 * @var string
	 */
	private const XML_ENCODING = 'UTF-8';

	/**
	 * Public DTD identifier of a Song 1.0 document.
	 *
	 * @var string
	 */
	private const DTD_PUBLIC = '-//JABURJAK//DTD Song 1.0//EN';

	/**
	 * System DTD identifier of a Song 1.0 document.
	 *
	 * @var string
	 */
	private const DTD_SYSTEM = 'https://chords.jaburjak.cz/dtd/song-1.dtd';

	/**
	 * URI of the Song 1.0 XML schema.
	 *
	 * @var string
	 */
	private const XML_NAMESPACE = 'https://chords.jaburjak.cz/schema/song-1.xsd';

	/**
	 * Default value of `count` attribute of `<repeat>`.
	 *
	 * @var int
	 */
	private const REPEAT_COUNT_DEFAULT = 2;

	/**
	 * XML document.
	 *
	 * @var DOMDocument
	 */
	private $dom;

	/**
	 * Current hierarchical structure of the built XML.
	 *
	 * Example: At the bottom of the stack is `<song>`. When `<lyrics>` starts to get processed, it is added onto the
	 * stack. When a `<strophe>` starts to get processed, it is added onto the stack. Same for other elements, lets say
	 * it ends with `<verse>`. When the verse is processed, it is removed from the stack and another can be added. When
	 * the entire paragraph is processed, it is removed from the stack and another one can be added onto the strophe
	 * that has come to the top of the stack.
	 *
	 * @var SplStack
	 */
	private $fragmentStack;

	public function __construct() {
		$implementation = new DOMImplementation();

		$doctype = $implementation->createDocumentType('song', self::DTD_PUBLIC, self::DTD_SYSTEM);

		$this->dom = new DOMDocument(self::XML_VERSION, self::XML_ENCODING);

		$this->dom->formatOutput = true;
		$this->dom->appendChild($doctype);

		$this->fragmentStack = new SplStack();
		$this->fragmentStack->push($this->dom);
	}

	public function saveXml(): string {
		return $this->dom->saveXML();
	}

	/**
	 * @inheritdoc
	 */
	public function visitChord(Chord $chord): void {
		$element = $this->dom->createElement('chord', $chord->getName());

		if (!$chord->isPrint()) {
			$element->setAttribute('print', 'false');
		}

		$this->fragmentStack->top()->appendChild($element);
	}

	/**
	 * @inheritdoc
	 */
	public function visitParagraph(Paragraph $paragraph): void {
		$element = $this->dom->createElement('paragraph');

		$this->fragmentStack->push($element);

		foreach ($paragraph->getNodes() as $node) {
			$node->accept($this);
		}

		$this->fragmentStack->pop();
		$this->fragmentStack->top()->appendChild($element);
	}

	/**
	 * @inheritdoc
	 */
	public function visitRepeat(Repeat $repeat): void {
		$element = $this->dom->createElement('repeat');

		if ($repeat->getCount() !== self::REPEAT_COUNT_DEFAULT) {
			$element->setAttribute('count', (string) $repeat->getCount());
		}

		$this->fragmentStack->push($element);

		foreach ($repeat->getNodes() as $node) {
			$node->accept($this);
		}

		$this->fragmentStack->pop();
		$this->fragmentStack->top()->appendChild($element);
	}

	/**
	 * @inheritdoc
	 */
	public function visitSong(Song $song): void {
		$element = $this->dom->createElement('song');

		$element->setAttribute('xmlns', self::XML_NAMESPACE);
		$element->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
		$element->setAttribute('xsi:noNamespaceSchemaLocation', self::XML_NAMESPACE);

		$this->fragmentStack->push($element);

		$song->getInfo()->accept($this);
		$song->getLyrics()->accept($this);

		$this->fragmentStack->pop();
		$this->fragmentStack->top()->appendChild($element);
	}

	/**
	 * @inheritdoc
	 */
	public function visitSongInfo(SongInfo $info): void {
		$element = $this->dom->createElement('info');

		$title = $this->dom->createElement('title', $info->getTitle());
		$element->appendChild($title);

		if ($info->getAuthor() !== null) {
			$author = $this->dom->createElement('author', $info->getAuthor());
			$element->appendChild($author);
		}

		$this->fragmentStack->top()->appendChild($element);
	}

	/**
	 * @inheritdoc
	 */
	public function visitSongLyrics(SongLyrics $lyrics): void {
		$element = $this->dom->createElement('lyrics');

		$this->fragmentStack->push($element);

		foreach ($lyrics->getNodes() as $node) {
			$node->accept($this);
		}

		$this->fragmentStack->pop();
		$this->fragmentStack->top()->appendChild($element);
	}

	/**
	 * @inheritdoc
	 */
	public function visitStrophe(Strophe $strophe): void {
		$element = $this->dom->createElement('strophe');

		if ($strophe->getLabel() !== null) {
			$element->setAttribute('label', $strophe->getLabel());
		}

		$this->fragmentStack->push($element);

		foreach ($strophe->getNodes() as $node) {
			$node->accept($this);
		}

		$this->fragmentStack->pop();
		$this->fragmentStack->top()->appendChild($element);
	}

	/**
	 * @inheritdoc
	 */
	public function visitStropheReference(StropheReference $reference): void {
		$element = $this->dom->createElement('strophe-ref');

		if ($reference->getStrophe()->getLabel() === null) {
			throw new UnexpectedValueException('Cannot create a <strophe-ref> element pointing to an unlabeled <strophe>.');
		}

		$element->setAttribute('ref', $reference->getStrophe()->getLabel());

		$this->fragmentStack->top()->appendChild($element);
	}

	/**
	 * @inheritdoc
	 */
	public function visitText(Text $text): void {
		$node = $this->dom->createTextNode($text->getText());

		$this->fragmentStack->top()->appendChild($node);
	}

	/**
	 * @inheritdoc
	 */
	public function visitVerse(Verse $verse): void {
		$element = $this->dom->createElement('verse');

		$this->fragmentStack->push($element);

		foreach ($verse->getNodes() as $node) {
			$node->accept($this);
		}

		$this->fragmentStack->pop();
		$this->fragmentStack->top()->appendChild($element);
	}
}