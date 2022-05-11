<?php
/**
 * @author Jakub Jabůrek <jaburek.jakub@gmail.com>
 */

declare(strict_types=1);

namespace Chords\Chord\Export;

use Chords\Chord\Model\Chord;

/**
 * Transforms a chord into an SVG image.
 *
 * @package Chords\Chord\Export
 */
interface ChordSvgExporterInterface {
	/**
	 * Transforms the chord into an SVG image.
	 *
	 * @param Chord $chord chord to transform
	 * @return string SVG source
	 */
	public function toSvg(Chord $chord): string;
}