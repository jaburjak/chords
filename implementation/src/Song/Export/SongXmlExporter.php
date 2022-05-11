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
final class SongXmlExporter implements SongXmlExporterInterface {
	/**
	 * @inheritdoc
	 */
	public function toXml(Song $song): string {
		$visitor = new XmlExportVisitor();

		$song->accept($visitor);

		return $visitor->saveXml();
	}
}