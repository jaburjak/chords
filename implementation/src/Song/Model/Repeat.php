<?php
/**
 * @author Jakub Jabůrek <jaburek.jakub@gmail.com>
 */

declare(strict_types=1);

namespace Chords\Song\Model;

use InvalidArgumentException;
use Chords\Song\Export\VisitorInterface;

/**
 * Repeat block.
 *
 * @package Chords\Song\Model
 */
final class Repeat implements NodeContainer {
	/**
	 * Repeated content.
	 *
	 * @var Node[]
	 */
	private $nodes;

	/**
	 * How many times the contents should be repeated.
	 *
	 * @var int
	 */
	private $count;

	/**
	 * @inheritdoc
	 */
	public function getNodes(): array {
		return $this->nodes;
	}

	/**
	 * @return int repeat count
	 */
	public function getCount(): int {
		return $this->count;
	}

	/**
	 * @param Node[] $nodes repeated content
	 * @param int    $count repeat count
	 */
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