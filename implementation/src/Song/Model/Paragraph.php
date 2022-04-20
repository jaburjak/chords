<?php
declare(strict_types=1);

namespace Chords\Song\Model;

use InvalidArgumentException;

final class Paragraph implements NodeContainer {
	use NodeContainerEquatableTrait;

	/**
	 * @var Node[]
	 */
	private $nodes;

	/**
	 * @return Node[]
	 */
	public function getNodes(): array {
		return $this->nodes;
	}

	/**
	 * @param Node[] $nodes
	 */
	public function __construct(array $nodes) {
		array_walk($nodes, function ($node, $index) {
			if (!$node instanceof Verse && !$node instanceof Repeat) {
				throw new InvalidArgumentException(sprintf(
					'Argument $nodes expected to contain only Verse and Repeat elements, %s found.',
					is_object($node) ? get_class($node) : gettype($node)
				));
			}
		});

		$this->nodes = array_values($nodes);
	}
}