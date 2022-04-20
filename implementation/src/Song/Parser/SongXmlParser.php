<?php
declare(strict_types=1);

namespace Chords\Song\Parser;

use SimpleXMLElement;
use Chords\Exception\InvalidXmlException;
use Chords\Song\Model\Song;
use Chords\Song\Model\SongInfo;
use Chords\Song\Model\SongLyrics;

final class SongXmlParser implements SongXmlParserInterface {
	public function parse(string $xml): Song {
		try {
			$sxml = @simplexml_load_string($xml);
		} catch (\Exception $e) {
			throw new InvalidXmlException('Could not parse the given XML string.', 0, $e);
		}

		if (!$sxml) {
			throw new InvalidXmlException('Could not parse the given XML string.');
		}

		return new Song($this->parseInfo($sxml), $this->parseLyrics($sxml));
	}

	private function parseInfo(SimpleXMLElement $sxml): SongInfo {
		if (!isset($sxml->info)) {
			throw new InvalidXmlException('Missing <info> element.');
		}

		$parser = new SongInfoXmlParser();

		return $parser->parse($sxml->info);
	}

	private function parseLyrics(SimpleXMLElement $sxml): SongLyrics {
		if (!isset($sxml->lyrics)) {
			throw new InvalidXmlException('Missing <lyrics> element.');
		}

		$parser = new SongLyricsXmlParser();

		return $parser->parse($sxml->lyrics);
	}
}