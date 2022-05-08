<?php
declare(strict_types=1);

namespace Chords\Song\Model;

use InvalidArgumentException;
use Chords\Song\Export\VisitorInterface;

final class Repeat implements NodeContainer {
	/**
	 * @var Node[]
	 */
	private $nodes;

	/**
	 * @var int
	 */
	private $count;

	/**
	 * @inheritdoc
	 */
	public function getNodes(): array {
		return $this->nodes;
	}

	public function getCount(): int {
		return $this->count;
	}

	public function __construct(array $nodes, int $count) {
		array_walk($nodes, function ($node, $index) {
			if (!$node instanceof Node) {
				throw new InvalidArgumentException(sprintf(
					'Argument $nodes expected to contain only Node elements, %s found.',
					is_object($node) ? get_class($node) : gettype($node)
				));
			}
		});

		$this->nodes = array_values($nodes);

		if ($count < 2) {
			throw new InvalidArgumentException('Argument $count cannot be less than two, you gave %d.', $count);
		}

		$this->count = $count;
	}

	/**
	 * @inheritdoc
	 */
	public function accept(VisitorInterface $visitor): void {
		$visitor->visitRepeat($this);
	}
}