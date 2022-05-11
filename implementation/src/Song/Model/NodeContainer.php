<?php
/**
 * @author Jakub Jabůrek <jaburek.jakub@gmail.com>
 */

declare(strict_types=1);

namespace Chords\Song\Model;

/**
 * Lyrical node containing other nodes.
 *
 * @package Chords\Song\Model
 */
interface NodeContainer extends Node {
	/**
	 * @return Node[] contained nodes
	 */
	public function getNodes(): array;
}