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
		if ($strings <= 0) {
			throw new DomainException(sprintf(
				'Argument $strings must be greater than zero, you passed %d.',
				$strings
			));
		}

		$this->strings = $strings;

		if ($frets <= 0) {
			throw new DomainException(sprintf('Argument $frets must be greater than zero, you passed %d.', $frets));
		}

		$this->frets = $frets;

		if ($fretOffset < 0) {
			throw new DomainException(sprintf(
				'Argument $fretOffset must be greater or equal to zero, you passed %d.',
				$fretOffset
			));
		}

		if ($frets <= $fretOffset) {
			throw new DomainException(sprintf(
				'Argument $frets must be greater than $fretOffset, %d is not greater than %d.',
				$frets,
				$fretOffset
			));
		}

		$this->fretOffset = $fretOffset;

		$coords = [];

		array_walk($notes, function ($element) use ($strings, $frets, $fretOffset, &$coords) {
			if (!$element instanceof ChordNote) {
				throw new InvalidArgumentException(sprintf(
					'Argument $notes expected to contain only ChordNote elements, %s found.',
					is_object($element) ? get_class($element) : gettype($element)
				));
			}

			foreach ($element->getString() as $string) {
				if ($string > $strings) {
					throw new DomainException(sprintf(
						'Maximum allowed value of "string" property of ChordNote is %d, you gave %d.',
						$strings,
						$string
					));
				}
			}

			if ($element->getFret() > $frets) {
				throw new DomainException(sprintf(
					'Maximum allowed value of "fret" property of ChordNote is %d, you gave %d.',
					$frets,
					$element->getFret()
				));
			}

			if ($element->getFret() <= $fretOffset) {
				throw new DomainException(sprintf(
					'Property "fret" of ChordNote must be greater than $fretOffset, %d is not greater than %d.',
					$element->getFret(),
					$fretOffset
				));
			}

			for ($i = $element->getString()[0]; $i <= ($element->getString()[1] ?? $element->getString()[0]); $i++) {
				$coords[] = sprintf('%d:%d', $i, $element->getFret());
			}
		});

		if (count($coords) !== count(array_unique($coords))) {
			throw new DomainException('All elements in $notes must have unique coordinates.');
		}

		$this->notes = array_values($notes);

		$coords = array_map(function ($element) use ($strings) {
			if (!$element instanceof ChordMark) {
				throw new InvalidArgumentException(sprintf(
					'Argument $marks expected to contain only ChordMark elements, %s found.',
					is_object($element) ? get_class($element) : gettype($element)
				));
			}

			if ($element->getString() > $strings) {
				throw new DomainException(sprintf(
					'Maximum allowed value of "string" property of ChordMark is %d, you gave %d.',
					$strings,
					$element->getString()
				));
			}

			return sprintf('%d', $element->getString());
		}, $marks);

		if (count($coords) !== count(array_unique($coords))) {
			throw new DomainException('All elements in $marks must have unique coordinates.');
		}

		$this->marks = array_values($marks);
	}
}