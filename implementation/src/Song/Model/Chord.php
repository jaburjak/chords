<?php
/**
 * @author Jakub Jabůrek <jaburek.jakub@gmail.com>
 */

declare(strict_types=1);

namespace Chords\Song\Model;

use InvalidArgumentException;
use Chords\Song\Export\VisitorInterface;

/**
 * Chord within song lyrics.
 *
 * @package Chords\Song\Model
 */
final class Chord implements Node {
	/**
	 * Chord name.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Should the chord be included in printed output?
	 *
	 * @var bool
	 */
	private $print;

	/**
	 * @return string chord name
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * @return bool include in print output?
	 */
	public function isPrint(): bool {
		return $this->print;
	}

	/**
	 * @param string $name  chord name
	 * @param bool   $print include in print output?
	 */
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