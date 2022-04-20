<?php
declare(strict_types=1);

namespace Chords\Song\Model;

use InvalidArgumentException;

final class Strophe implements NodeContainer {
	use NodeContainerEquatableTrait {
		equals as private nodesEqual;
	}

	/**
	 * @var Node[]
	 */
	private $nodes;

	/**
	 * @var string|null
	 */
	private $label;

	/**
	 * @return Node[]
	 */
	public function getNodes(): array {
		return $this->nodes;
	}

	public function getLabel(): ?string {
		return $this->label;
	}

	/**
	 * @param Node[] $nodes
	 * @param string|null $label
	 */
	public function __construct(array $nodes, ?string $label) {
		array_walk($nodes, function ($node, $index) {
			if (!$node instanceof Paragraph && !$node instanceof Repeat) {
				throw new InvalidArgumentException(sprintf(
					'Argument $nodes expected to contain only Paragraph and Repeat elements, %s found.',
					is_object($node) ? get_class($node) : gettype($node)
				));
			}
		});

		$this->nodes = array_values($nodes);

		if ($label === '') {
			throw new InvalidArgumentException('Argument $label cannot be an empty string.');
		}

		$this->label = $label;
	}

	/**
	 * @inheritdoc
	 */
	public function equals($other): bool {
		return $this->nodesEqual($other) &&
		       $this->getLabel() === $other->getLabel();
	}
}