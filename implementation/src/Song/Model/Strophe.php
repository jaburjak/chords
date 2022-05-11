<?php
/**
 * @author Jakub Jabůrek <jaburek.jakub@gmail.com>
 */

declare(strict_types=1);

namespace Chords\Song\Model;

use InvalidArgumentException;
use Chords\Song\Export\VisitorInterface;

/**
 * Strophe.
 *
 * @package Chords\Song\Model
 */
final class Strophe implements NodeContainer {
	/**
	 * Strophe content.
	 *
	 * @var Node[]
	 */
	private $nodes;

	/**
	 * Strophe label.
	 *
	 * @var string|null
	 */
	private $label;

	/**
	 * @inheritdoc
	 */
	public function getNodes(): array {
		return $this->nodes;
	}

	/**
	 * @return string|null label
	 */
	public function getLabel(): ?string {
		return $this->label;
	}

	/**
	 * @param Node[]      $nodes content
	 * @param string|null $label label
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
	public function accept(VisitorInterface $visitor): void {
		$visitor->visitStrophe($this);
	}
}