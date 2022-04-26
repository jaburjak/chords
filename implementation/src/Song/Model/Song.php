<?php
declare(strict_types=1);

namespace Chords\Song\Model;

use Chords\Contracts\EquatableInterface;
use Chords\Song\Export\VisitorInterface;

final class Song implements EquatableInterface {
	/**
	 * @var SongInfo
	 */
	private $info;

	/**
	 * @var SongLyrics
	 */
	private $lyrics;

	public function getInfo(): SongInfo {
		return $this->info;
	}

	public function getLyrics(): SongLyrics {
		return $this->lyrics;
	}

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

	/**
	 * @inheritdoc
	 */
	public function equals($other): bool {
		return $other instanceof Song &&
		       $this->getInfo()->equals($other->getInfo()) &&
		       $this->getLyrics()->equals($other->getLyrics());
	}
}