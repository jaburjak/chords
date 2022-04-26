<?php
declare(strict_types=1);

namespace Chords\Song\Model;

use InvalidArgumentException;
use Chords\Song\Export\VisitorInterface;

final class Chord implements Node {
	/**
	 * @var string
	 */
	private $name;

	public function getName(): string {
		return $this->name;
	}

	public function __construct(string $name) {
		if ($name === '') {
			throw new InvalidArgumentException('Argument $name cannot be an empty string.');
		}

		$this->name = $name;
	}

	/**
	 * @inheritdoc
	 */
	public function accept(VisitorInterface $visitor): void {
		$visitor->visitChord($this);
	}

	/**
	 * @inheritdoc
	 */
	public function equals($other): bool {
		return $other instanceof Chord &&
		       $this->getName() === $other->getName();
	}
}