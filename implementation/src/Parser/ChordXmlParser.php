<?php
declare(strict_types=1);

namespace Chords\Parser;

use SimpleXMLElement;
use Chords\Chord\Model\Chord;
use Chords\Chord\Model\ChordDefinition;
use Chords\Chord\Model\ChordMark;
use Chords\Chord\Model\ChordNote;

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

		return new Chord($name, $this->parseDefinition($sxml));
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

		return new ChordDefinition($strings, $frets, $this->parseNotes($sxml), $this->parseMarks($sxml));
	}

	/**
	 * @return ChordNote[]
	 */
	private function parseNotes(SimpleXMLElement $sxml): array {
		$notes = [];

		/** @var SimpleXMLElement $note */
		foreach ($sxml->def->{'def-note'} as $note) {
			$string = (string) $note->{'note-string'};

			if ($string === '') {
				throw new InvalidXmlException('Missing or empty <note-string> element.');
			}

			if (!is_numeric($string)) {
				throw new InvalidXmlException('Encountered non-numeric value in <note-string>.');
			}

			$string = intval($string);

			$fret = (string) $note->{'note-fret'};

			if ($fret === '') {
				throw new InvalidXmlException('Missing or empty <note-fret> element.');
			}

			if (!is_numeric($fret)) {
				throw new InvalidXmlException('Encountered non-numeric value in <note-fret>.');
			}

			$fret = intval($fret);

			$notes[] = new ChordNote($string, $fret);
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