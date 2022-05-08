<?php
declare(strict_types=1);

namespace Chords\Chord\Model;

use DomainException;

final class ChordMark {
	/**
	 * @var string
	 */
	public const TYPE_MUTED = 'muted';

	/**
	 * @var string
	 */
	public const TYPE_OPEN = 'open';

	/**
	 * @var int
	 */
	private $string;

	/**
	 * @var string
	 */
	private $type;

	public function getString(): int {
		return $this->string;
	}

	public function getType(): string {
		return $this->type;
	}

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