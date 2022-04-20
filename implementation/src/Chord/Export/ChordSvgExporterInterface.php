<?php
declare(strict_types=1);

namespace Chords\Chord\Export;

use Chords\Chord\Model\Chord;

interface ChordSvgExporterInterface {
	public function toSvg(Chord $chord): string;
}