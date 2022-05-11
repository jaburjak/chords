<?php
/**
 * @author Jakub Jabůrek <jaburek.jakub@gmail.com>
 */

declare(strict_types=1);

namespace Chords\Song\Model;

use Chords\Song\Export\VisitorInterface;

/**
 * Lyrical node.
 *
 * @package Chords\Song\Model
 */
interface Node {
	/**
	 * Accepts the given visitor.
	 *
	 * @param VisitorInterface $visitor visitor
	 * @return void
	 */
	public function accept(VisitorInterface $visitor): void;
}