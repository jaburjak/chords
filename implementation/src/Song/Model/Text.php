<?php
declare(strict_types=1);

namespace Chords\Song\Model;

final class Text implements Node {
	/**
	 * @var string
	 */
	private $text;

	public function getText(): string {
		return $this->text;
	}

	public function __construct(string $text) {
		$this->text = $text;
	}

	/**
	 * @inheritdoc
	 */
	public function equals($other): bool {
		return $other instanceof Text &&
		       $this->getText() === $other->getText();
	}
}