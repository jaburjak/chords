<?php
declare(strict_types=1);

namespace Chords\Chord\Model;

use InvalidArgumentException;
use DomainException;

final class ChordNote {
	/**
	 * @var int[]
	 */
	private $string;

	/**
	 * @var int
	 */
	private $fret;

	public function getString(): array {
		return $this->string;
	}

	public function getFret(): int {
		return $this->fret;
	}

	public function __construct(array $string, int $fret) {
		if (count($string) !== 1 && count($string) !== 2) {
			throw new InvalidArgumentException(sprintf(
				'Array in argument $string must have one or two elements, you gave %d.',
				count($string)
			));
		}

		array_walk($string, function ($element) {
			if (!is_int($element)) {
				throw new InvalidArgumentException(sprintf(
					'Argument $string expected to be an array of integers, %s found.',
					gettype($element)
				));
			}

			if ($element <= 0) {
				throw new DomainException(sprintf('Numbers in argument $string must be greater than zero, you passed %d.', $element));
			}
		});

		sort($string, \SORT_NUMERIC);

		if (count($string) === 2 && $string[0] === $string[1]) {
			throw new DomainException(sprintf('Numbers in argument $string must be different.'));
		}

		$this->string = $string;

		if ($fret <= 0) {
			throw new DomainException(sprintf('Argument $fret must be greater than zero, you passed %d.', $fret));
		}

		$this->fret = $fret;
	}
}