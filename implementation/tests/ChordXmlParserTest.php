<?php
declare(strict_types=1);

use Chords\Chord\Model\Chord;
use Chords\Chord\Model\ChordDefinition;
use Chords\Chord\Model\ChordMark;
use Chords\Chord\Model\ChordNote;
use Chords\Parser\ChordXmlParser;
use PHPUnit\Framework\TestCase;

final class ChordXmlParserTest extends TestCase {
	/**
	 * @dataProvider xmlProvider
	 */
	public function testValidXml($xml, $expected): void {
		$parser = new ChordXmlParser();

		$this->assertTrue($expected->equals($parser->parse($xml)));
	}

	public function xmlProvider(): array {
		return [
			'empty chord' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE chord PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/chord-1.dtd">
<chord>
	<name>empty</name>
	<def>
		<def-strings>1</def-strings>
		<def-frets>1</def-frets>
	</def>
</chord>
XML,
				new Chord('empty', new ChordDefinition(1, 1, [], []))]
		];
	}

	/**
	 * @dataProvider invalidXmlProvider
	 */
	public function testInvalidXml($xml, $expectedException): void {
		$parser = new ChordXmlParser();

		$this->expectException($expectedException);

		$parser->parse($xml);
	}

	public function invalidXmlProvider(): array {
		return [
			'missing <name>' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE chord PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/chord-1.dtd">
<chord>
	<def>
		<def-strings>6</def-strings>
		<def-frets>5</def-frets>
		<def-note>
			<note-string>6</note-string>
			<note-fret>1</note-fret>
		</def-note>
	</def>
</chord>
XML,
				'\Chords\Parser\InvalidXmlException'],
			'missing <def>' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE chord PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/chord-1.dtd">
<chord>
	<name>Dmi</name>
</chord>
XML,
				'\Chords\Parser\InvalidXmlException'],
			'missing <def-strings>' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE chord PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/chord-1.dtd">
<chord>
	<name>Dmi</name>
	<def>
		<def-frets>5</def-frets>
		<def-note>
			<note-string>6</note-string>
			<note-fret>1</note-fret>
		</def-note>
	</def>
</chord>
XML,
				'\Chords\Parser\InvalidXmlException'],
			'missing <def-frets>' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE chord PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/chord-1.dtd">
<chord>
	<name>Dmi</name>
	<def>
		<def-strings>6</def-strings>
		<def-note>
			<note-string>6</note-string>
			<note-fret>1</note-fret>
		</def-note>
	</def>
</chord>
XML,
				'\Chords\Parser\InvalidXmlException'],
			'non-numeric <def-strings>' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE chord PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/chord-1.dtd">
<chord>
	<name>Dmi</name>
	<def>
		<def-strings>abc</def-strings>
		<def-frets>5</def-frets>
		<def-note>
			<note-string>6</note-string>
			<note-fret>1</note-fret>
		</def-note>
	</def>
</chord>
XML,
				'\Chords\Parser\InvalidXmlException'],
			'non-numeric <def-frets>' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE chord PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/chord-1.dtd">
<chord>
	<name>Dmi</name>
	<def>
		<def-strings>6</def-strings>
		<def-frets>abc</def-frets>
		<def-note>
			<note-string>6</note-string>
			<note-fret>1</note-fret>
		</def-note>
	</def>
</chord>
XML,
				'\Chords\Parser\InvalidXmlException'],
			'zero in <def-strings>' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE chord PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/chord-1.dtd">
<chord>
	<name>Dmi</name>
	<def>
		<def-strings>0</def-strings>
		<def-frets>5</def-frets>
		<def-note>
			<note-string>6</note-string>
			<note-fret>1</note-fret>
		</def-note>
	</def>
</chord>
XML,
				'\DomainException'],
			'zero in <def-frets>' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE chord PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/chord-1.dtd">
<chord>
	<name>Dmi</name>
	<def>
		<def-strings>6</def-strings>
		<def-frets>0</def-frets>
		<def-note>
			<note-string>6</note-string>
			<note-fret>1</note-fret>
		</def-note>
	</def>
</chord>
XML,
				'\DomainException'],
			'missing <note-string>' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE chord PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/chord-1.dtd">
<chord>
	<name>Dmi</name>
	<def>
		<def-strings>6</def-strings>
		<def-frets>5</def-frets>
		<def-note>
			<note-fret>1</note-fret>
		</def-note>
	</def>
</chord>
XML,
				'\Chords\Parser\InvalidXmlException'],
			'missing <note-fret>' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE chord PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/chord-1.dtd">
<chord>
	<name>Dmi</name>
	<def>
		<def-strings>6</def-strings>
		<def-frets>5</def-frets>
		<def-note>
			<note-string>6</note-string>
		</def-note>
	</def>
</chord>
XML,
				'\Chords\Parser\InvalidXmlException'],
			'zero in <note-string>' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE chord PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/chord-1.dtd">
<chord>
	<name>Dmi</name>
	<def>
		<def-strings>6</def-strings>
		<def-frets>5</def-frets>
		<def-note>
			<note-string>0</note-string>
			<note-fret>1</note-fret>
		</def-note>
	</def>
</chord>
XML,
				'\DomainException'],
			'zero in <note-fret>' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE chord PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/chord-1.dtd">
<chord>
	<name>Dmi</name>
	<def>
		<def-strings>6</def-strings>
		<def-frets>5</def-frets>
		<def-note>
			<note-string>6</note-string>
			<note-fret>0</note-fret>
		</def-note>
	</def>
</chord>
XML,
				'\DomainException'],
			'too large <note-string>' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE chord PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/chord-1.dtd">
<chord>
	<name>Dmi</name>
	<def>
		<def-strings>6</def-strings>
		<def-frets>5</def-frets>
		<def-note>
			<note-string>7</note-string>
			<note-fret>1</note-fret>
		</def-note>
	</def>
</chord>
XML,
				'\DomainException'],
			'too large <note-fret>' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE chord PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/chord-1.dtd">
<chord>
	<name>Dmi</name>
	<def>
		<def-strings>6</def-strings>
		<def-frets>5</def-frets>
		<def-note>
			<note-string>6</note-string>
			<note-fret>6</note-fret>
		</def-note>
	</def>
</chord>
XML,
				'\DomainException'],
			'duplicate <def-note>' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE chord PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/chord-1.dtd">
<chord>
	<name>Dmi</name>
	<def>
		<def-strings>6</def-strings>
		<def-frets>5</def-frets>
		<def-note>
			<note-string>6</note-string>
			<note-fret>1</note-fret>
		</def-note>
		<def-note>
			<note-string>6</note-string>
			<note-fret>1</note-fret>
		</def-note>
	</def>
</chord>
XML,
				'\DomainException'],
			'missing <mark-string>' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE chord PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/chord-1.dtd">
<chord>
	<name>Dmi</name>
	<def>
		<def-strings>6</def-strings>
		<def-frets>5</def-frets>
		<def-note>
			<note-string>6</note-string>
			<note-fret>1</note-fret>
		</def-note>
		<def-mark>
			<mark-type>muted</mark-type>
		</def-mark>
	</def>
</chord>
XML,
				'\Chords\Parser\InvalidXmlException'],
			'missing <mark-type>' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE chord PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/chord-1.dtd">
<chord>
	<name>Dmi</name>
	<def>
		<def-strings>6</def-strings>
		<def-frets>5</def-frets>
		<def-note>
			<note-string>6</note-string>
			<note-fret>1</note-fret>
		</def-note>
		<def-mark>
			<mark-string>1</mark-string>
		</def-mark>
	</def>
</chord>
XML,
				'\Chords\Parser\InvalidXmlException'],
			'zero in <mark-string>' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE chord PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/chord-1.dtd">
<chord>
	<name>Dmi</name>
	<def>
		<def-strings>6</def-strings>
		<def-frets>5</def-frets>
		<def-note>
			<note-string>6</note-string>
			<note-fret>1</note-fret>
		</def-note>
		<def-mark>
			<mark-string>0</mark-string>
			<mark-type>muted</mark-type>
		</def-mark>
	</def>
</chord>
XML,
				'\DomainException'],
			'too large <mark-string>' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE chord PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/chord-1.dtd">
<chord>
	<name>Dmi</name>
	<def>
		<def-strings>6</def-strings>
		<def-frets>5</def-frets>
		<def-note>
			<note-string>6</note-string>
			<note-fret>1</note-fret>
		</def-note>
		<def-mark>
			<mark-string>7</mark-string>
			<mark-type>muted</mark-type>
		</def-mark>
	</def>
</chord>
XML,
				'\DomainException'],
			'invalid <mark-type>' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE chord PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/chord-1.dtd">
<chord>
	<name>Dmi</name>
	<def>
		<def-strings>6</def-strings>
		<def-frets>5</def-frets>
		<def-note>
			<note-string>6</note-string>
			<note-fret>1</note-fret>
		</def-note>
		<def-mark>
			<mark-string>0</mark-string>
			<mark-type>mted</mark-type>
		</def-mark>
	</def>
</chord>
XML,
				'\DomainException'],
			'duplicate <def-mark>' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE chord PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/chord-1.dtd">
<chord>
	<name>Dmi</name>
	<def>
		<def-strings>6</def-strings>
		<def-frets>5</def-frets>
		<def-note>
			<note-string>6</note-string>
			<note-fret>1</note-fret>
		</def-note>
		<def-mark>
			<mark-string>1</mark-string>
			<mark-type>muted</mark-type>
		</def-mark>
		<def-mark>
			<mark-string>1</mark-string>
			<mark-type>open</mark-type>
		</def-mark>
	</def>
</chord>
XML,
				'\DomainException']
		];
	}
}