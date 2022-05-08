<?php
declare(strict_types=1);

namespace Chords\Song\Model;

use Chords\Song\Export\VisitorInterface;

final class SongInfo {
	/**
	 * @var string
	 */
	private $title;

	/**
	 * @var string|null
	 */
	private $author;

	public function getTitle(): string {
		return $this->title;
	}

	public function getAuthor(): ?string {
		return $this->author;
	}

	public function __construct(string $title, ?string $author) {
		$this->title = $title;
		$this->author = $author;
	}

	/**
	 * @inheritdoc
	 */
	public function accept(VisitorInterface $visitor): void {
		$visitor->visitSongInfo($this);
	}
}