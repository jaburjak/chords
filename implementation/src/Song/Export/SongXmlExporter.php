<?php
declare(strict_types=1);

namespace Chords\Song\Export;

use Chords\Song\Model\Song;

final class SongXmlExporter implements SongXmlExporterInterface {
	public function toXml(Song $song): string {
		$visitor = new XmlExportVisitor();

		$song->accept($visitor);

		return $visitor->saveXml();
	}
}