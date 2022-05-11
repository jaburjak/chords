<?php
/**
 * @author Jakub Jabůrek <jaburek.jakub@gmail.com>
 */

declare(strict_types=1);

namespace Chords\Song\Export;

use Chords\Song\Model\Chord;
use Chords\Song\Model\Paragraph;
use Chords\Song\Model\Repeat;
use Chords\Song\Model\Song;
use Chords\Song\Model\SongInfo;
use Chords\Song\Model\SongLyrics;
use Chords\Song\Model\Strophe;
use Chords\Song\Model\StropheReference;
use Chords\Song\Model\Text;
use Chords\Song\Model\Verse;

/**
 * Visitor pattern for song objects.
 *
 * @package Chords\Song\Export
 */
interface VisitorInterface {
	/**
	 * Process a chord.
	 *
	 * @param Chord $chord chord
	 * @return void
	 */
	public function visitChord(Chord $chord): void;

	/**
	 * Process a paragraph.
	 *
	 * @param Paragraph $paragraph paragraph
	 * @return void
	 */
	public function visitParagraph(Paragraph $paragraph): void;

	/**
	 * Process a repeat.
	 *
	 * @param Repeat $repeat repeat
	 * @return void
	 */
	public function visitRepeat(Repeat $repeat): void;

	/**
	 * Process entire song.
	 *
	 * @param Song $song song
	 * @return void
	 */
	public function visitSong(Song $song): void;

	/**
	 * Process song metadata.
	 *
	 * @param SongInfo $info metadata
	 * @return void
	 */
	public function visitSongInfo(SongInfo $info): void;

	/**
	 * Process song lyrics.
	 *
	 * @param SongLyrics $lyrics lyrics
	 * @return void
	 */
	public function visitSongLyrics(SongLyrics $lyrics): void;

	/**
	 * Process a strophe.
	 *
	 * @param Strophe $strophe strophe
	 * @return void
	 */
	public function visitStrophe(Strophe $strophe): void;

	/**
	 * Process a strophe reference.
	 *
	 * @param StropheReference $reference strophe reference
	 * @return void
	 */
	public function visitStropheReference(StropheReference $reference): void;

	/**
	 * Process plain text.
	 *
	 * @param Text $text text
	 * @return void
	 */
	public function visitText(Text $text): void;

	/**
	 * Process a verse.
	 *
	 * @param Verse $verse verse
	 * @return void
	 */
	public function visitVerse(Verse $verse): void;
}