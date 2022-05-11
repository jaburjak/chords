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
final class SongHtmlExporter implements SongHtmlExporterInterface {
	/**
	 * @inheritdoc
	 */
	public function toHtml(Song $song): string {
		$visitor = new HtmlExportVisitor();

		$song->accept($visitor);

		return $visitor->saveHtml();
	}
}