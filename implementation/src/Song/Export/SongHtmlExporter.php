<?php
declare(strict_types=1);

namespace Chords\Song\Export;

use Chords\Song\Model\Song;

final class SongHtmlExporter implements SongHtmlExporterInterface {
	public function toHtml(Song $song): string {
		$visitor = new HtmlExportVisitor();

		$song->accept($visitor);

		return $visitor->saveHtml();
	}
}