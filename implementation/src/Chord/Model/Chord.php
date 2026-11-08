<?php
/**
 * @author Jakub Jabůrek <jaburek.jakub@gmail.com>
 */

declare(strict_types=1);

namespace Chords\Chord\Model;

use InvalidArgumentException;

/**
 * Chord of a string instrument.
 *
 * @package Chords\Chord\Model
 */
final class Chord {
	/**
	 * Chord name.
	 */
	private string $name;

	/**
	 * Alternative chord names.
	 *
	 * @var string[]
	 */
	private array $alternativeNames;

	/**
	 * Chord definition.
	 */
	private ChordDefinition $definition;

	/**
	 * @return string chord name
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * @return string[] alternative chord names
	 */
	public function getAlternativeNames(): array {
		return $this->alternativeNames;
	}

	/**
	 * @return ChordDefinition chord definition
	 */
	public function getDefinition(): ChordDefinition {
		return $this->definition;
	}

	/**
	 * @param string          $name             chord name
	 * @param ChordDefinition $definition       chord definition
	 * @param string[]        $alternativeNames alternative chord names
	 */
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