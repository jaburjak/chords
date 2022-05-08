<?php
declare(strict_types=1);

namespace Chords\Chord\Model;

use DomainException;
use InvalidArgumentException;

final class ChordDefinition {
	/**
	 * @var int
	 */
	private $strings;

	/**
	 * @var int
	 */
	private $fretOffset;

	/**
	 * @var int
	 */
	private $frets;

	/**
	 * @var ChordNote[]
	 */
	private $notes;

	/**
	 * @var ChordMark[]
	 */
	private $marks;

	public function getStrings(): int {
		return $this->strings;
	}

	public function getFretOffset(): int {
		return $this->fretOffset;
	}

	public function getFrets(): int {
		return $this->frets;
	}

	/**
	 * @return iterable<ChordNote>
	 */
	public function getNotes(): iterable {
		return $this->notes;
	}

	/**
	 * @return iterable<ChordMark>
	 */
	public function getMarks(): iterable {
		return $this->marks;
	}

	public function __construct(int $strings, int $frets, int $fretOffset, array $notes, array $marks) {
		$this->strings = $this->validateStrings($strings);
		$this->frets = $this->validateFrets($frets);
		$this->fretOffset = $this->validateFretOffset($fretOffset);
		$this->notes = $this->validateNotes($notes);
		$this->marks = $this->validateMarks($marks);
	}

	private function validateStrings(int $strings): int {
		if ($strings <= 0) {
			throw new DomainException(sprintf(
				'Argument $strings must be greater than zero, you passed %d.',
				$strings
			));
		}

		return $strings;
	}

	private function validateFrets(int $frets): int {
		if ($frets <= 0) {
			throw new DomainException(sprintf('Argument $frets must be greater than zero, you passed %d.', $frets));
		}

		return $frets;
	}

	private function validateFretOffset(int $fretOffset): int {
		if ($fretOffset < 0) {
			throw new DomainException(sprintf(
				'Argument $fretOffset must be greater or equal to zero, you passed %d.',
				$fretOffset
			));
		}

		if ($this->frets <= $fretOffset) {
			throw new DomainException(sprintf(
				'Argument $frets must be greater than $fretOffset, %d is not greater than %d.',
				$this->frets,
				$fretOffset
			));
		}

		return $fretOffset;
	}

	private function validateNotes(array $notes): array {
		$coords = [];

		array_walk($notes, function ($element) use (&$coords) {
			if (!$element instanceof ChordNote) {
				throw new InvalidArgumentException(sprintf(
					'Argument $notes expected to contain only ChordNote elements, %s found.',
					is_object($element) ? get_class($element) : gettype($element)
				));
			}

			foreach ($element->getString() as $string) {
				if ($string > $this->strings) {
					throw new DomainException(sprintf(
						'Maximum allowed value of "string" property of ChordNote is %d, you gave %d.',
						$this->strings,
						$string
					));
				}
			}

			if ($element->getFret() > $this->frets) {
				throw new DomainException(sprintf(
					'Maximum allowed value of "fret" property of ChordNote is %d, you gave %d.',
					$this->frets,
					$element->getFret()
				));
			}

			if ($element->getFret() <= $this->fretOffset) {
				throw new DomainException(sprintf(
					'Property "fret" of ChordNote must be greater than $fretOffset, %d is not greater than %d.',
					$element->getFret(),
					$this->fretOffset
				));
			}

			for ($i = $element->getString()[0]; $i <= ($element->getString()[1] ?? $element->getString()[0]); $i++) {
				$coords[] = sprintf('%d:%d', $i, $element->getFret());
			}
		});

		if (count($coords) !== count(array_unique($coords))) {
			throw new DomainException('All elements in $notes must have unique coordinates.');
		}

		return array_values($notes);
	}

	private function validateMarks(array $marks): array {
		$coords = array_map(function ($element) {
			if (!$element instanceof ChordMark) {
				throw new InvalidArgumentException(sprintf(
					'Argument $marks expected to contain only ChordMark elements, %s found.',
					is_object($element) ? get_class($element) : gettype($element)
				));
			}

			if ($element->getString() > $this->strings) {
				throw new DomainException(sprintf(
					'Maximum allowed value of "string" property of ChordMark is %d, you gave %d.',
					$this->strings,
					$element->getString()
				));
			}

			return sprintf('%d', $element->getString());
		}, $marks);

		if (count($coords) !== count(array_unique($coords))) {
			throw new DomainException('All elements in $marks must have unique coordinates.');
		}

		return array_values($marks);
	}
}