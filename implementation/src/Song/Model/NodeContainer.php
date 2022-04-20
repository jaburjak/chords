<?php
declare(strict_types=1);

namespace Chords\Song\Model;

interface NodeContainer extends Node {
	/**
	 * @return Node[]
	 */
	public function getNodes(): array;
}