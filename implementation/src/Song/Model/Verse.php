<?php
/**
 * @author Jakub Jabůrek <jaburek.jakub@gmail.com>
 */

declare(strict_types=1);

namespace Chords\Song\Model;

use InvalidArgumentException;
use Chords\Song\Export\VisitorInterface;

/**
 * Lyrics verse.
 *
 * @package Chords\Song\Model
 */
final class Verse implements NodeContainer {
	/**
	 * Verse content.
	 *
	 * @var Node[]
	 */
	private $nodes;

	/**
	 * @inheritdoc
	 */
	public function getNodes(): array {
		return $this->nodes;
	}

	/**
	 * @param Node[] $nodes verse content
	 */
	public function __construct(array $nodes) {
		array_walk($nodes, function ($node, $index) {
			if (!$node instanceof Text && !$node instanceof Chord && !$node instanceof Repeat) {
				throw new InvalidArgumentException(sprintf(
					'Argument $nodes expected to contain only Text, Chord and Repeat elements, %s found.',
					is_object($node) ? get_class($node) : gettype($node)
				));
			}
		});

		$this->nodes = array_values($nodes);
	}

	/**
	 * @inheritdoc
	 */
	public function accept(VisitorInterface $visitor): void {
		$visitor->visitVerse($this);
	}
}