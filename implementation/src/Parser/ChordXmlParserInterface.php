<?php
declare(strict_types=1);

namespace Chords\Parser;

use Chords\Chord\Model\Chord;

interface ChordXmlParserInterface {
	public function parse(string $xml): Chord;
}