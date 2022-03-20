<?php
declare(strict_types=1);

namespace Chords\Model\Chord;

use Chords\Contracts\EquatableInterface;

final class Chord implements EquatableInterface {
	private string $name;

	private ChordDefinition $definition;

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