<?php
declare(strict_types=1);

namespace Chords\Song\Model;

use Chords\Contracts\EquatableInterface;
use Chords\Song\Export\VisitorInterface;

interface Node extends EquatableInterface {
	public function accept(VisitorInterface $visitor): void;
}