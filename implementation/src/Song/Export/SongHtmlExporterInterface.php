<?php
/**
 * @author Jakub Jabůrek <jaburek.jakub@gmail.com>
 */

declare(strict_types=1);

namespace Chords\Song\Export;

use Chords\Song\Model\Song;

/**
 * Transforms song lyrics to HTML.
 *
 * @package Chords\Song\Export
 */
interface SongHtmlExporterInterface {
	/**
	 * Transforms song lyrics to HTML.
	 *
	 * @param Song $song song
	 * @return string HTML
	 */
	public function toHtml(Song $song): string;
}