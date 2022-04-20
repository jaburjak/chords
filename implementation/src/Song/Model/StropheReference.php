<?php
declare(strict_types=1);

namespace Chords\Song\Model;

final class StropheReference implements Node {
	/**
	 * @var Strophe
	 */
	private $strophe;

	public function getStrophe(): Strophe {
		return $this->strophe;
	}

	public function __construct(Strophe $strophe) {
		$this->strophe = $strophe;
	}

	/**
	 * @inheritdoc
	 */
	public function equals($other): bool {
		return $other instanceof StropheReference &&
		       $this->getStrophe()->equals($other->getStrophe());
	}
}