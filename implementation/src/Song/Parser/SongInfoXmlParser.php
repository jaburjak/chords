<?php
declare(strict_types=1);

namespace Chords\Song\Parser;

use SimpleXMLElement;
use Chords\Exception\InvalidXmlException;
use Chords\Song\Model\SongInfo;

final class SongInfoXmlParser {
	public function parse(SimpleXMLElement $sxml): SongInfo {
		if ((string) $sxml->title === '') {
			throw new InvalidXmlException('Missing or empty <title> element.');
		}

		$title = (string) $sxml->title;

		if ((string) $sxml->author === '') {
			$author = null;
		} else {
			$author = (string) $sxml->author;
		}

		return new SongInfo($title, $author);
	}
}