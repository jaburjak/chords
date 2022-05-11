<?php
/**
 * @author Jakub Jabůrek <jaburek.jakub@gmail.com>
 */

declare(strict_types=1);

namespace Chords\Chord\Parser;

use DomainException;
use InvalidArgumentException;
use Chords\Chord\Model\Chord;
use Chords\Exception\InvalidXmlException;

/**
 * Chord 1.0 XML document parser.
 *
 * @package Chords\Chord\Parser
 */
interface ChordXmlParserInterface {
	/**
	 * Parses the given XML document.
	 *
	 * @param string $xml XML string
	 * @return Chord parsed object
	 * @throws InvalidXmlException
	 * @throws InvalidArgumentException
	 * @throws DomainException
	 */
	public function parse(string $xml): Chord;
}