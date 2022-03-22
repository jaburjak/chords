<?php
declare(strict_types=1);

namespace Chords\Chord\Model;

use Chords\Contracts\EquatableInterface;

final class Chord implements EquatableInterface {
	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var ChordDefinition
	 */
	private $definition;

	public function getName(): string {
		return $this->name;
	}

	public function getDefinition(): ChordDefinition {
		return $this->definition;
	}

	public function __construct(string $name, ChordDefinition $definition) {
		$this->name = $name;
		$this->definition = $definition;
	}

	/**
	 * @inheritdoc
	 */
	public function equals($other): bool {
		return $other instanceof Chord &&
		       $this->getName() === $other->getName() &&
		       $this->getDefinition()->equals($other->getDefinition());
	}
}