<?php
/**
 * @author Jakub Jabůrek <jaburek.jakub@gmail.com>
 */

declare(strict_types=1);

namespace Chords\Song\Model;

use InvalidArgumentException;
use Chords\Song\Export\VisitorInterface;

/**
 * Strophe paragraph.
 *
 * @package Chords\Song\Model
 */
final class Paragraph implements NodeContainer {
	/**
	 * Nodes within the paragraph.
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
	 * @param Node[] $nodes verses or repeats
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

	/**
	 * @inheritdoc
	 */
	public function accept(VisitorInterface $visitor): void {
		$visitor->visitParagraph($this);
	}
}