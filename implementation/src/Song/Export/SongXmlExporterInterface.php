<?php
declare(strict_types=1);

namespace Chords\Song\Export;

use Chords\Song\Model\Song;

interface SongXmlExporterInterface {
	public function toXml(Song $song): string;
}