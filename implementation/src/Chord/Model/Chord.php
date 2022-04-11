<?php
declare(strict_types=1);

namespace Chords\Chord\Model;

use InvalidArgumentException;
use Chords\Contracts\EquatableInterface;

final class Chord implements EquatableInterface {
	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string[]
	 */
	private $alternativeNames;

	/**
	 * @var ChordDefinition
	 */
	private $definition;

	public function getName(): string {
		return $this->name;
	}

	/**
	 * @return string[]
	 */
	public function getAlternativeNames(): array {
		return $this->alternativeNames;
	}

	public function getDefinition(): ChordDefinition {
		return $this->definition;
	}

	public function __construct(string $name, ChordDefinition $definition, array $alternativeNames = []) {
		$this->name = $name;
		$this->definition = $definition;

		array_walk($alternativeNames, function ($name, $key) {
			if (!is_string($name)) {
				throw new InvalidArgumentException(sprintf(
					'Argument $alternativeNames expected to contain only string elements, %s found.',
					gettype($element)
				));
			}
		});

		$this->alternativeNames = array_values($alternativeNames);
	}

	/**
	 * @inheritdoc
	 */
	public function equals($other): bool {
		return $other instanceof Chord &&
		       $this->getName() === $other->getName() &&
		       count($this->getAlternativeNames()) === count(array_intersect($this->getAlternativeNames(), $other->getAlternativeNames())) &&
		       $this->getDefinition()->equals($other->getDefinition());
	}
}