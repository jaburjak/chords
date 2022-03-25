<?php
declare(strict_types=1);

use Chords\Chord\Model\Chord;
use Chords\Chord\Model\ChordDefinition;
use Chords\Chord\Model\ChordMark;
use Chords\Chord\Model\ChordNote;
use PHPUnit\Framework\TestCase;

final class ChordTest extends TestCase {
	private const PROVIDER_SIZE = 32;

	/**
	 * @dataProvider dataProvider
	 */
	public function testEquals($a, $b, $equals): void {
		if ($equals) {
			$this->assertTrue($a->equals($b));
			$this->assertTrue($b->equals($a));
		} else {
			$this->assertFalse($a->equals($b));
			$this->assertFalse($b->equals($a));
		}
	}

	public function dataProvider(): \Generator {
		for ($i = 0; $i < self::PROVIDER_SIZE; $i++) {
			$a = $b = $this->generateData();
			$equals = $i % 2 === 0;

			if (!$equals) {
				do {
					$b = $this->generateData();
				} while ($a['name'] === $b['name']);
			}

			yield [$this->dataToObject($a), $this->dataToObject($b), $equals];
		}
	}

	private function generateData(): array {
		$name = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ#'), 0, rand(1, 20));

		$strings = rand(1, 128);
		$frets = rand(1, 128);
		$fretOffset = rand(0, $frets - 1);

		$notes = [];

		for ($i = 0; $i < $strings * $frets; $i++) {
			if (rand(0, 1) === 1) continue;

			$string = rand(1, $strings);
			$fret = rand($fretOffset + 1, $frets);

			$notes[sprintf('%d:%d', $string, $fret)] = [$string, $fret];
		}

		$marks = [];

		for ($i = 0; $i < $strings; $i++) {
			if (rand(0, 1) === 1) continue;

			$string = rand(1, $strings);
			$type = rand(0, 1) === 1 ? ChordMark::TYPE_OPEN : ChordMark::TYPE_MUTED;

			$marks[sprintf('%d', $string)] = [$string, $type];
		}

		return [
			'name' => $name,
			'strings' => $strings,
			'frets' => $frets,
			'fretOffset' => $fretOffset,
			'notes' => $notes,
			'marks' => $marks
		];
	}

	private function dataToObject(array $data): Chord {
		return new Chord(
			$data['name'],
			new ChordDefinition(
				$data['strings'],
				$data['frets'],
				$data['fretOffset'],
				array_map(function (array $note) {
					return new ChordNote($note[0], $note[1]);
				}, $data['notes']),
				array_map(function (array $mark) {
					return new ChordMark($mark[0], $mark[1]);
				}, $data['marks'])
			)
		);
	}
}