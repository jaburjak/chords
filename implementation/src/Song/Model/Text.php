<?php
/**
 * @author Jakub Jabůrek <jaburek.jakub@gmail.com>
 */

declare(strict_types=1);

namespace Chords\Song\Model;

use Chords\Song\Export\VisitorInterface;

/**
 * Plain text within the lyrics.
 *
 * @package Chords\Song\Model
 */
final class Text implements Node {
	/**
	 * Text.
	 *
	 * @var string
	 */
	private $text;

	/**
	 * @return string text
	 */
	public function getText(): string {
		return $this->text;
	}

	/**
	 * @param string $text text
	 */
	public function __construct(string $text) {
		$this->text = $text;
	}

	/**
	 * @inheritdoc
	 */
	public function accept(VisitorInterface $visitor): void {
		$visitor->visitText($this);
	}
}