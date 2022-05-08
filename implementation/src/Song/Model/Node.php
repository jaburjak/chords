<?php
declare(strict_types=1);

namespace Chords\Song\Model;

use Chords\Song\Export\VisitorInterface;

interface Node {
	public function accept(VisitorInterface $visitor): void;
}