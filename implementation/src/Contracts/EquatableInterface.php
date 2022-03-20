<?php
declare(strict_types=1);

namespace Chords\Contracts;

interface EquatableInterface {
	public function equals($other): bool;
}