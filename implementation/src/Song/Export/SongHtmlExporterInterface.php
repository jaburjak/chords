<?php
declare(strict_types=1);

namespace Chords\Song\Export;

use Chords\Song\Model\Song;

interface SongHtmlExporterInterface {
	public function toHtml(Song $song): string;
}