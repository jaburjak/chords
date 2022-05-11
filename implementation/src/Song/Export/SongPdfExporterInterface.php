<?php
/**
 * @author Jakub Jabůrek <jaburek.jakub@gmail.com>
 */

declare(strict_types=1);

namespace Chords\Song\Export;

use Chords\Song\Model\Song;

/**
 * Transforms song into printable PDF document.
 *
 * @package Chords\Song\Export
 */
interface SongPdfExporterInterface {
	/**
	 * Transforms the song into printable PDF document.
	 *
	 * @param Song             $song    song
	 * @param PdfExportOptions $options export settings
	 * @return string PDF file
	 */
	public function toPdf(Song $song, PdfExportOptions $options): string;
}