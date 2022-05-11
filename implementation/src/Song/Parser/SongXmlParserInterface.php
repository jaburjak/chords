<?php
/**
 * @author Jakub Jabůrek <jaburek.jakub@gmail.com>
 */

declare(strict_types=1);

namespace Chords\Song\Parser;

use Chords\Song\Model\Song;

/**
 * Song 1.0 XML document parser.
 *
 * @package Chords\Song\Parser
 */
interface SongXmlParserInterface {
	/**
	 * Parses the given XML document.
	 *
	 * @param string $xml XML string
	 * @return Song parsed object
	 * @throws InvalidXmlException
	 * @throws InvalidArgumentException
	 * @throws DomainException
	 */
	public function parse(string $xml): Song;
}