<?php
declare(strict_types=1);

namespace Chords\Chord\Model;

use InvalidArgumentException;

final class Chord {
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
					gettype($name)
				));
			}
		});

		$this->alternativeNames = array_values($alternativeNames);
	}
}