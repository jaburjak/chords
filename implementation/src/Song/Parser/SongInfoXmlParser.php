<?php
/**
 * @author Jakub Jabůrek <jaburek.jakub@gmail.com>
 */

declare(strict_types=1);

namespace Chords\Song\Parser;

use SimpleXMLElement;
use Chords\Exception\InvalidXmlException;
use Chords\Song\Model\SongInfo;

/**
 * Song metadata XML parser.
 *
 * @package Chords\Song\Parser
 */
final class SongInfoXmlParser {
	/**
	 * Parses song XML metadata.
	 *
	 * @param SimpleXMLElement $sxml metadata element
	 * @return SongInfo parsed object
	 * @throws InvalidXmlElement
	 */
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