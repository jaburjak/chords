<?php
declare(strict_types=1);

namespace Chords\Song\Parser;

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

final class SongLyricsXmlParser {
	public function parse(SimpleXMLElement $sxml): SongLyrics {
		$labeled = $this->extractLabeledStrophes($sxml);

		return new SongLyrics($this->parseLyrics($sxml, $labeled));
	}

	/**
	 * @return Strophe[]
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
	 * @return Node[]
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