<?php
declare(strict_types=1);

namespace Chords\Song\Export;

use Chords\Song\Model\Song;

interface SongPdfExporterInterface {
	public function toPdf(Song $song, PdfExportOptions $options): string;
}