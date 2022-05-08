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

	/**
	 * @var bool
	 */
	private $print;

	public function getName(): string {
		return $this->name;
	}

	public function isPrint(): bool {
		return $this->print;
	}

	public function __construct(string $name, bool $print) {
		if ($name === '') {
			throw new InvalidArgumentException('Argument $name cannot be an empty string.');
		}

		$this->name = $name;
		$this->print = $print;
	}

	/**
	 * @inheritdoc
	 */
	public function accept(VisitorInterface $visitor): void {
		$visitor->visitChord($this);
	}
}