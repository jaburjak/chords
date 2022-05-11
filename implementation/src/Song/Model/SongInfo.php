<?php
/**
 * @author Jakub Jabůrek <jaburek.jakub@gmail.com>
 */

declare(strict_types=1);

namespace Chords\Song\Model;

use Chords\Song\Export\VisitorInterface;

/**
 * Song metadata.
 *
 * @package Chords\Song\Model
 */
final class SongInfo {
	/**
	 * Song title.
	 *
	 * @var string
	 */
	private $title;

	/**
	 * Song author.
	 *
	 * @var string|null
	 */
	private $author;

	/**
	 * @return string song title
	 */
	public function getTitle(): string {
		return $this->title;
	}

	/**
	 * @return string|null song author
	 */
	public function getAuthor(): ?string {
		return $this->author;
	}

	/**
	 * @param string      $title  song title
	 * @param string|null $author song author
	 */
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