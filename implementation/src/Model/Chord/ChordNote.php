<?php
declare(strict_types=1);

namespace Chords\Model\Chord;

use DomainException;
use Chords\Contracts\EquatableInterface;

final class ChordNote implements EquatableInterface {
	private int $string;

	private int $fret;

	public function getString(): int {
		return $this->string;
	}

	public function getFret(): int {
		return $this->fret;
	}

	public function __construct(int $string, int $fret) {
		if ($string <= 0) {
			throw new DomainException(sprintf('Argument $string must be greater than zero, you passed %d.', $string));
		}

		$this->string = $string;

		if ($fret <= 0) {
			throw new DomainException(sprintf('Argument $fret must be greater than zero, you passed %d.', $fret));
		}

		$this->fret = $fret;
	}

	/**
	 * @inheritdoc
	 */
	public function equals($other): bool {
		return $other instanceof ChordNote &&
		       $this->getString() === $other->getString() &&
		       $this->getFret() === $other->getFret();
	}
}