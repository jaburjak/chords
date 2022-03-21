<?php
declare(strict_types=1);

namespace Chords\Chord\Model;

use DomainException;
use Chords\Contracts\EquatableInterface;

final class ChordMark implements EquatableInterface {
	public const TYPE_MUTED = 'muted';

	public const TYPE_OPEN = 'open';

	private int $string;

	private string $type;

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

	/**
	 * @inheritdoc
	 */
	public function equals($other): bool {
		return $other instanceof ChordMark &&
		       $this->getString() === $other->getString() &&
		       $this->getType() === $other->getType();
	}
}