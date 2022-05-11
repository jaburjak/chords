<?php
/**
 * @author Jakub Jabůrek <jaburek.jakub@gmail.com>
 */

declare(strict_types=1);

namespace Chords\Chord\Model;

use DomainException;

/**
 * String mark.
 *
 * @package Chords\Chord\Model
 */
final class ChordMark {
	/**
	 * Muted string.
	 *
	 * @var string
	 */
	public const TYPE_MUTED = 'muted';

	/**
	 * Open string.
	 *
	 * @var string
	 */
	public const TYPE_OPEN = 'open';

	/**
	 * String number.
	 *
	 * @var int
	 */
	private $string;

	/**
	 * Mark type.
	 *
	 * @var string
	 */
	private $type;

	/**
	 * @return int string number
	 */
	public function getString(): int {
		return $this->string;
	}

	/**
	 * @return string mark type
	 */
	public function getType(): string {
		return $this->type;
	}

	/**
	 * @param int    $string string number
	 * @param string $type   mark type
	 */
	public function __construct(int $string, string $type) {
		if ($string <= 0) {
			throw new DomainException(sprintf('Argument $string must be greater than zero, you passed %d.', $string));
		}

		$this->string = $string;

		if ($type !== self::TYPE_MUTED && $type !== self::TYPE_OPEN) {
			throw new DomainException(sprintf(
				'Argument $type must be one of ChordMark::TYPE_* constants, you passed "%s".',
				$type
			));
		}

		$this->type = $type;
	}
}