<?php
declare(strict_types=1);

namespace Chords\Chord\Model;

use DomainException;
use InvalidArgumentException;
use Chords\Contracts\EquatableInterface;

final class ChordDefinition implements EquatableInterface {
	/**
	 * @var int
	 */
	private $strings;

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

	public function __construct(int $strings, int $frets, array $notes, array $marks) {
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

		$coords = array_map(function ($element) use ($strings, $frets) {
			if (!$element instanceof ChordNote) {
				throw new InvalidArgumentException(sprintf(
					'Argument $notes expected to contain only ChordNote elements, %s found.',
					is_object($element) ? get_class($element) : gettype($element)
				));
			}

			if ($element->getString() > $strings) {
				throw new DomainException(sprintf(
					'Maximum allowed value of "string" property of ChordNote is %d, you gave %d.',
					$strings,
					$element->getString()
				));
			}

			if ($element->getFret() > $frets) {
				throw new DomainException(sprintf(
					'Maximum allowed value of "fret" property of ChordNote is %d, you gave %d.',
					$frets,
					$element->getFret()
				));
			}

			return sprintf('%d:%d', $element->getString(), $element->getFret());
		}, $notes);

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

	/**
	 * @inheritdoc
	 */
	public function equals($other): bool {
		return $other instanceof ChordDefinition &&
		       $this->getStrings() === $other->getStrings() &&
		       $this->getFrets() === $other->getFrets() &&
		       count(array_udiff($this->getNotes(), $other->getNotes(), function (ChordNote $a, ChordNote $b) {
		       	return $a->equals($b) ? 0 : ($a->getString() + $a->getFret() - $b->getString() - $b->getFret());
		       })) === 0 &&
		       count(array_udiff($this->getMarks(), $other->getMarks(), function (ChordMark $a, ChordMark $b) {
		       	return $a->equals($b) ? 0 : ($a->getString() + - $b->getString());
		       })) === 0;
	}
}