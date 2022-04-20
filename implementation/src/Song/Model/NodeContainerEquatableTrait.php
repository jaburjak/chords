<?php
declare(strict_types=1);

namespace Chords\Song\Model;

trait NodeContainerEquatableTrait {
	/**
	 * @inheritdoc
	 */
	public function equals($other): bool {
		if (!$other instanceof self || count($this->getNodes()) !== count($other->getNodes())) {
			return false;
		}

		$otherNodes = $other->getNodes();

		foreach ($this->getNodes() as $key => $node) {
			if (!$otherNodes[$key]->equals($node)) {
				return false;
			}
		}

		return true;
	}
}