<?php
/**
 * @author Jakub Jabůrek <jaburek.jakub@gmail.com>
 */

declare(strict_types=1);

namespace Chords\Song\Export;

use Chords\Song\Model\Song;

/**
 * Saves a song as a Song 1.0 XML document.
 *
 * @package Chords\Song\Export
 */
interface SongXmlExporterInterface {
	/**
	 * Saves the song as a Song 1.0 XML document.
	 *
	 * @param Song $song song
	 * @return string XML document
	 */
	public function toXml(Song $song): string;
}