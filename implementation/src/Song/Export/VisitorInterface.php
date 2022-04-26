<?php
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

interface VisitorInterface {
	public function visitChord(Chord $chord): void;

	public function visitParagraph(Paragraph $paragraph): void;

	public function visitRepeat(Repeat $repeat): void;

	public function visitSong(Song $song): void;

	public function visitSongInfo(SongInfo $info): void;

	public function visitSongLyrics(SongLyrics $lyrics): void;

	public function visitStrophe(Strophe $strophe): void;

	public function visitStropheReference(StropheReference $reference): void;

	public function visitText(Text $text): void;

	public function visitVerse(Verse $verse): void;
}