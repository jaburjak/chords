<?php
/**
 * @author Jakub Jabůrek <jaburek.jakub@gmail.com>
 */

declare(strict_types=1);

namespace Chords\Song\Parser;

use DomainException;
use DOMElement;
use InvalidArgumentException;
use SimpleXMLElement;
use Chords\Exception\InvalidXmlException;
use Chords\Song\Model\Chord;
use Chords\Song\Model\Node;
use Chords\Song\Model\Paragraph;
use Chords\Song\Model\Repeat;
use Chords\Song\Model\SongLyrics;
use Chords\Song\Model\Strophe;
use Chords\Song\Model\StropheReference;
use Chords\Song\Model\Text;
use Chords\Song\Model\Verse;

/**
 * Song lyrics XML parser.
 *
 * @package Chords\Song\Parser
 */
final class SongLyricsXmlParser {
	/**
	 * Parses song lyrics.
	 *
	 * @param SimpleXMLElement $sxml lyrics element
	 * @return SongLyrics parsed object
	 * @throws InvalidXmlException
	 * @throws InvalidArgumentException
	 * @throws DomainException
	 */
	public function parse(SimpleXMLElement $sxml): SongLyrics {
		$labeled = $this->extractLabeledStrophes($sxml);

		return new SongLyrics($this->parseLyrics($sxml, $labeled));
	}

	/**
	 * Returns strophes with a label.
	 *
	 * The keys of the returned array are strophe labels.
	 *
	 * @param SimpleXMLElement $sxml lyrics element
	 * @return Strophe[] labeled strophes
	 * @throws InvalidXmlException
	 * @throws InvalidArgumentException
	 * @throws DomainException
	 */
	private function extractLabeledStrophes(SimpleXMLElement $sxml): array {
		/** @var Strophe[] $strophes */
		$strophes = [];

		foreach ($sxml->children() as $node) {
			switch (strtolower($node->getName())) {
				case 'strophe':
					$strophe = $this->parseStrophe($node);

					if ($strophe->getLabel() !== null) {
						$strophes[$strophe->getLabel()] = $strophe;
					}
					break;
				case 'repeat':
					foreach ($this->extractLabeledStrophes($node) as $label => $strophe) {
						$strophes[$label] = $strophe;
					}
					break;
				default:
					break;
			}
		}

		return $strophes;
	}

	/**
	 * Parses song lyrics.
	 *
	 * @param SimpleXMLElement $sxml            lyrics element
	 * @param Strophe[]        $labeledStrophes already parsed strophes with labels
	 * @return Node[] lyrics content
	 * @throws InvalidXmlException
	 * @throws InvalidArgumentException
	 * @throws DomainException
	 */
	private function parseLyrics(SimpleXMLElement $sxml, array $labeledStrophes): array {
		$nodes = [];

		foreach ($sxml->children() as $node) {
			switch (strtolower($node->getName())) {
				case 'strophe':
					$nodes[] = $this->parseStrophe($node);
					break;
				case 'strophe-ref':
					$reference = (string) ($node['ref'] ?? '');

					if ($reference === '') {
						throw new InvalidXmlException('Attribute "ref" of <strophe-ref> must not be empty.');
					}

					if (!isset($labeledStrophes[$reference])) {
						throw new InvalidXmlException(sprintf(
							'Attribute "ref" of <strophe-ref> must refer to an existing <strophe>, no element with label "%s" found.',
							$reference
						));
					}

					$nodes[] = new StropheReference($labeledStrophes[$reference]);
					break;
				case 'repeat':
					$nodes[] = $this->parseRepeat($node, ['lyrics', $labeledStrophes]);
					break;
				default:
					throw new InvalidXmlException(sprintf('Element <lyrics> cannot have <%s> as its child.', $node->getName()));
			}
		}

		return $nodes;
	}

	/**
	 * Parses a strophe.
	 *
	 * @param SimpleXMLElement $sxml strophe element
	 * @return Strophe parsed object
	 * @throws InvalidXmlException
	 * @throws InvalidArgumentException
	 * @throws DomainException
	 */
	private function parseStrophe(SimpleXMLElement $sxml): Strophe {
		/** @var string|null $label */
		$label = (string) ($sxml['label'] ?? '');

		if ($label === '') {
			$label = null;
		}

		/** @var Node[] $nodes */
		$nodes = [];

		foreach ($sxml->children() as $node) {
			switch (strtolower($node->getName())) {
				case 'paragraph':
					$nodes[] = $this->parseParagraph($node);
					break;
				case 'repeat':
					$nodes[] = $this->parseRepeat($node, ['strophe']);
					break;
				default:
					throw new InvalidXmlException('Element <strophe> cannot have <%s> as its child.', $node->getName());
			}
		}

		return new Strophe($nodes, $label);
	}

	/**
	 * Parses a paragraph.
	 *
	 * @param SimpleXMLElement $sxml paragraph element
	 * @return Paragraph parsed object
	 * @throws InvalidXmlException
	 * @throws InvalidArgumentException
	 * @throws DomainException
	 */
	private function parseParagraph(SimpleXMLElement $sxml): Paragraph {
		/** @var Node[] $nodes */
		$nodes = [];

		foreach ($sxml->children() as $node) {
			switch (strtolower($node->getName())) {
				case 'verse':
					$nodes[] = $this->parseVerse($node);
					break;
				case 'repeat':
					$nodes[] = $this->parseRepeat($node, ['paragraph']);
					break;
				default:
					throw new InvalidXmlException('Element <paragraph> cannot have <%s> as its child.', $node->getName());
			}
		}

		return new Paragraph($nodes);
	}

	/**
	 * Parses a verse.
	 *
	 * @param SimpleXMLElement $sxml verse element
	 * @return Verse parsed object
	 * @throws InvalidXmlException
	 * @throws InvalidArgumentException
	 * @throws DomainException
	 */
	private function parseVerse(SimpleXMLElement $sxml): Verse {
		$dom = dom_import_simplexml($sxml);

		/** @var Node[] $nodes */
		$nodes = [];

		foreach ($dom->childNodes as $node) {
			if ($node->nodeType === \XML_TEXT_NODE) {
				$nodes[] = new Text($node->textContent);
			} else {
				switch (strtolower($node->nodeName)) {
					case 'chord':
						$nodes[] = $this->parseChord($node);
						break;
					case 'repeat':
						$nodes[] = $this->parseRepeat(simplexml_import_dom($node), ['verse']);
						break;
					default:
						throw new InvalidXmlException('Element <verse> cannot have <%s> as its child.', $node->nodeName);
				}
			}
		}

		return new Verse($nodes);
	}

	/**
	 * Parses a chord.
	 *
	 * @param DOMElement $dom chord element
	 * @return Chord parsed object
	 * @throws InvalidXmlException
	 * @throws InvalidArgumentException
	 */
	private function parseChord(DOMElement $dom): Chord {
		switch (mb_strtolower((string) $dom->getAttribute('print'))) {
			case '':
			case 'true':
			case '1':
				$print = true;
				break;
			case 'false':
			case '0':
				$print = false;
				break;
			default:
				throw new InvalidXmlException('Attribute "print" must have a boolean value.');
		}

		return new Chord($dom->textContent, $print);
	}

	/**
	 * Parses a repeat element.
	 *
	 * @param SimpleXMLElement $sxml    repeat element
	 * @param array            $context context where the repeat element appeared
	 * @return Strophe parsed object
	 * @throws InvalidXmlException
	 * @throws InvalidArgumentException
	 * @throws DomainException
	 */
	private function parseRepeat(SimpleXMLElement $sxml, array $context): Repeat {
		$count = $sxml['count'] ?? null;

		if ($count === null) {
			$count = 2;
		} else {
			$count = (string) $count;

			if (!is_numeric($count)) {
				throw new InvalidXmlException('Encountered non-numeric value in attribute "count" of <repeat>.');
			}

			$count = intval($count);
		}

		/** @var Node[] $nodes */
		$nodes = [];

		switch ($context[0]) {
			case 'lyrics':
				$nodes = $this->parseLyrics($sxml, $context[1]);
				break;
			case 'strophe':
				$strophe = $this->parseStrophe($sxml);
				$nodes = $strophe->getNodes();
				break;
			case 'paragraph':
				$paragraph = $this->parseParagraph($sxml);
				$nodes = $paragraph->getNodes();
				break;
			case 'verse':
				$verse = $this->parseVerse($sxml);
				$nodes = $verse->getNodes();
				break;
			default:
				throw new InvalidArgumentException('Unrecognized value in argument $context.');
		}

		return new Repeat($nodes, $count);
	}
}