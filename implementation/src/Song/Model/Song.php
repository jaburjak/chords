<?php
/**
 * @author Jakub Jabůrek <jaburek.jakub@gmail.com>
 */

declare(strict_types=1);

namespace Chords\Song\Model;

use Chords\Song\Export\VisitorInterface;

/**
 * Song.
 *
 * @package Chords\Song\Model
 */
final class Song {
	/**
	 * Song metadata.
	 *
	 * @var SongInfo
	 */
	private $info;

	/**
	 * Song lyrics.
	 *
	 * @var SongLyrics
	 */
	private $lyrics;

	/**
	 * @return SongInfo metadata
	 */
	public function getInfo(): SongInfo {
		return $this->info;
	}

	/**
	 * @return SongLyrics lyrics
	 */
	public function getLyrics(): SongLyrics {
		return $this->lyrics;
	}

	/**
	 * @param SongInfo   $info   metadata
	 * @param SongLyrics $lyrics lyrics
	 */
	public function __construct(SongInfo $info, SongLyrics $lyrics) {
		$this->info = $info;
		$this->lyrics = $lyrics;
	}

	/**
	 * @inheritdoc
	 */
	public function accept(VisitorInterface $visitor): void {
		$visitor->visitSong($this);
	}
}