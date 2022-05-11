<?php
/**
 * @author Jakub Jabůrek <jaburek.jakub@gmail.com>
 */

declare(strict_types=1);

namespace Chords\Song\Model;

use Chords\Song\Export\VisitorInterface;

/**
 * Reference to another strophe.
 *
 * @package Chords\Song\Model
 */
final class StropheReference implements Node {
	/**
	 * Referenced strophe.
	 *
	 * @var Strophe
	 */
	private $strophe;

	/**
	 * @return Strophe referenced strophe
	 */
	public function getStrophe(): Strophe {
		return $this->strophe;
	}

	/**
	 * @param Strophe $strophe referenced strophe
	 */
	public function __construct(Strophe $strophe) {
		$this->strophe = $strophe;
	}

	/**
	 * @inheritdoc
	 */
	public function accept(VisitorInterface $visitor): void {
		$visitor->visitStropheReference($this);
	}
}