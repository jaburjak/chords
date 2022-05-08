<?php
declare(strict_types=1);

namespace Chords\Chord\Parser;

use SimpleXMLElement;
use Chords\Chord\Model\Chord;
use Chords\Chord\Model\ChordDefinition;
use Chords\Chord\Model\ChordMark;
use Chords\Chord\Model\ChordNote;
use Chords\Exception\InvalidXmlException;

final class ChordXmlParser implements ChordXmlParserInterface {
	public function parse(string $xml): Chord {
		try {
			$sxml = @simplexml_load_string($xml);
		} catch (\Exception $e) {
			throw new InvalidXmlException('Could not parse the given XML string.', 0, $e);
		}

		if (!$sxml) {
			throw new InvalidXmlException('Could not parse the given XML string.');
		}

		$name = (string) $sxml->name;

		if ($name === '') {
			throw new InvalidXmlException('Missing or empty <name> element.');
		}

		if (!isset($sxml->def)) {
			throw new InvalidXmlException('Missing <def> element.');
		}

		return new Chord($name, $this->parseDefinition($sxml), $this->parseAlternativeNames($sxml));
	}

	/**
	 * @return string[]
	 */
	private function parseAlternativeNames(SimpleXMLElement $sxml): array {
		$alternativeNames = [];

		if (!isset($sxml->{'alt-names'})) {
			return $alternativeNames;
		}

		/** @var SimpleXMLElement $name */
		foreach ($sxml->{'alt-names'}->name as $name) {
			$name = (string) $name;

			if ($name === '') {
				throw new InvalidXmlException('Element <name> cannot be empty.');
			}

			$alternativeNames[] = $name;
		}

		return $alternativeNames;
	}

	private function parseDefinition(SimpleXMLElement $sxml): ChordDefinition {
		$strings = (string) $sxml->def->{'def-strings'};

		if ($strings === '') {
			throw new InvalidXmlException('Missing or empty <def-strings> element.');
		}

		if (!is_numeric($strings)) {
			throw new InvalidXmlException('Encountered non-numeric value in <def-strings>.');
		}

		$strings = intval($strings);

		$frets = (string) $sxml->def->{'def-frets'};

		if ($frets === '') {
			throw new InvalidXmlException('Missing or empty <def-frets> element.');
		}

		if (!is_numeric($frets)) {
			throw new InvalidXmlException('Encountered non-numeric value in <def-frets>.');
		}

		$frets = intval($frets);

		$fretOffset = (string) $sxml->def->{'def-frets'}[0]->attributes()['offset'];

		if ($fretOffset === '') {
			$fretOffset = 0;
		} else if (!is_numeric($fretOffset)) {
			throw new InvalidXmlException('Encountered non-numeric value in "offset" attribute of <def-frets>.');
		} else {
			$fretOffset = intval($fretOffset);
		}

		return new ChordDefinition($strings, $frets, $fretOffset, $this->parseNotes($sxml), $this->parseMarks($sxml));
	}

	/**
	 * @return ChordNote[]
	 */
	private function parseNotes(SimpleXMLElement $sxml): array {
		$notes = [];

		/** @var SimpleXMLElement $note */
		foreach ($sxml->def->{'def-note'} as $note) {
			$strings = [];

			/** @var SimpleXMLElement $string */
			foreach ($note->{'note-string'} as $string) {
				$string = (string) $string;

				if ($string === '') {
					throw new InvalidXmlException('Missing or empty <note-string> element.');
				}

				if (!is_numeric($string)) {
					throw new InvalidXmlException('Encountered non-numeric value in <note-string>.');
				}

				$strings[] = intval($string);
			}

			$fret = (string) $note->{'note-fret'};

			if ($fret === '') {
				throw new InvalidXmlException('Missing or empty <note-fret> element.');
			}

			if (!is_numeric($fret)) {
				throw new InvalidXmlException('Encountered non-numeric value in <note-fret>.');
			}

			$fret = intval($fret);

			$notes[] = new ChordNote($strings, $fret);
		}

		return $notes;
	}

	/**
	 * @return ChordMark[]
	 */
	private function parseMarks(SimpleXMLElement $sxml): array {
		$marks = [];

		/** @var SimpleXMLElement $mark */
		foreach ($sxml->def->{'def-mark'} as $mark) {
			$string = (string) $mark->{'mark-string'};

			if ($string === '') {
				throw new InvalidXmlException('Missing or empty <mark-string> element.');
			}

			if (!is_numeric($string)) {
				throw new InvalidXmlException('Encountered non-numeric value in <mark-string>.');
			}

			$string = intval($string);

			$type = (string) $mark->{'mark-type'};

			if ($type === '') {
				throw new InvalidXmlException('Missing or empty <mark-type> element.');
			}

			$marks[] = new ChordMark($string, $type);
		}

		return $marks;
	}
}