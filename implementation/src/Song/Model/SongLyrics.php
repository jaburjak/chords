<?php
declare(strict_types=1);

namespace Chords\Song\Model;

use InvalidArgumentException;

final class SongLyrics implements NodeContainer {
	use NodeContainerEquatableTrait;

	/**
	 * @var Node[]
	 */
	private $nodes;

	/**
	 * @inheritdoc
	 */
	public function getNodes(): array {
		return $this->nodes;
	}

	public function __construct(array $nodes) {
		array_walk($nodes, function ($node, $index) {
			if (!$node instanceof Node) {
				throw new InvalidArgumentException(sprintf(
					'Argument $nodes expected to contain only Node elements, %s found.',
					is_object($node) ? get_class($node) : gettype($node)
				));
			}
		});

		$this->nodes = array_values($nodes);
	}
}