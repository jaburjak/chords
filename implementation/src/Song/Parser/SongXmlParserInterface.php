<?php
declare(strict_types=1);

namespace Chords\Song\Parser;

use Chords\Song\Model\Song;

interface SongXmlParserInterface {
	public function parse(string $xml): Song;
}