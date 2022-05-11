<?php
declare(strict_types=1);

use Chords\Song\Model\Chord;
use Chords\Song\Model\Paragraph;
use Chords\Song\Model\Repeat;
use Chords\Song\Model\Song;
use Chords\Song\Model\SongInfo;
use Chords\Song\Model\SongLyrics;
use Chords\Song\Model\Strophe;
use Chords\Song\Model\StropheReference;
use Chords\Song\Model\Text;
use Chords\Song\Model\Verse;
use Chords\Song\Parser\SongXmlParser;
use PHPUnit\Framework\TestCase;

final class SongXmlParserTest extends TestCase {
	/**
	 * @dataProvider xmlProvider
	 */
	public function testValidXml($xml, $expected): void {
		$parser = new SongXmlParser();

		$this->assertEquals($expected, $parser->parse($xml));
	}

	public function xmlProvider(): array {
		$R = new Strophe([
			new Repeat([
				new Paragraph([
					new Verse([
						new Text('Na hradě Okoři '),
						new Chord('A7', true),
						new Text('světla už nehoří,')
					])
				]),
				new Paragraph([
					new Repeat([
						new Verse([
							new Chord('D', true),
							new Text('on jí sebral '),
							new Chord('A7', true),
							new Text('od komnaty klíč.')
						])
					], 2)
				])
			], 2)
		], 'R');

		$X = new Strophe([
			new Paragraph([
				new Verse([
					new Text('jako žádná ze sta,')
				])
			])
		], 'X');

		$Y = new Strophe([
			new Repeat([
				new Paragraph([
					new Repeat([
						new Verse([
							new Repeat([new Chord('D', true), new Text('Na')], 42), new Text(' Okoř je cesta')
						])
					], 2),
					new Verse([
						new Text('jako žádná ze sta,')
					])
				])
			], 2)
		], 'Y');

		return [
			'regular document' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE song PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/song-1.dtd">
<song xmlns="https://chords.jaburjak.cz/schema/song-1.xsd"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:noNamespaceSchemaLocation="https://chords.jaburjak.cz/schema/song-1.xsd">
	<info>
		<title>Okoř</title>
		<author>lidová</author>
	</info>
	<lyrics>
		<strophe>
			<paragraph>
				<verse><chord>D</chord>Na Okoř je cesta jako žádná ze sta,</verse>
				<verse><chord>A7</chord>vroubená je <repeat count="42">stromama</repeat><chord>D</chord>.</verse>
			</paragraph>
		</strophe>
		<strophe label="R">
			<repeat count="2">
				<paragraph>
					<verse>Na hradě Okoři <chord>A7</chord>světla už nehoří,</verse>
				</paragraph>
				<paragraph>
					<repeat>
						<verse><chord>D</chord>on jí sebral <chord>A7</chord>od komnaty klíč.</verse>
					</repeat>
				</paragraph>
			</repeat>
		</strophe>
		<strophe label="2">
			<paragraph>
				<verse><chord>D</chord>Jednoho dne z rána,</verse>
			</paragraph>
		</strophe>
		<strophe-ref ref="R" />
	</lyrics>
</song>
XML
			,new Song(
				new SongInfo('Okoř', 'lidová'),
				new SongLyrics([
					new Strophe([
						new Paragraph([
							new Verse([
								new Chord('D', true),
								new Text('Na Okoř je cesta jako žádná ze sta,')
							]),
							new Verse([
								new Chord('A7', true),
								new Text('vroubená je '),
								new Repeat([
									new Text('stromama')
								], 42),
								new Chord('D', true),
								new Text('.')
							])
						])
					], null),
					$R,
					new Strophe([
						new Paragraph([
							new Verse([
								new Chord('D', true),
								new Text('Jednoho dne z rána,')
							])
						])
					], '2'),
					new StropheReference($R)
				])
			)],
			'multiple strophes with the same label' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE song PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/song-1.dtd">
<song xmlns="https://chords.jaburjak.cz/schema/song-1.xsd"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:noNamespaceSchemaLocation="https://chords.jaburjak.cz/schema/song-1.xsd">
	<info>
		<title>.?!</title>
		<author>-&lt;-&gt;-</author>
	</info>
	<lyrics>
		<strophe label="X">
			<paragraph>
				<verse><chord>D</chord>Na Okoř je cesta</verse>
			</paragraph>
		</strophe>
		<strophe label="X">
			<paragraph>
				<verse>jako žádná ze sta,</verse>
			</paragraph>
		</strophe>
		<strophe-ref ref="X" />
	</lyrics>
</song>
XML
			,new Song(
				new SongInfo('.?!', '-<->-'),
				new SongLyrics([
					new Strophe([
						new Paragraph([
							new Verse([
								new Chord('D', true), new Text('Na Okoř je cesta')
							])
						])
					], 'X'),
					$X,
					new StropheReference($X)
				])
			)],
			'repeats' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE song PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/song-1.dtd">
<song xmlns="https://chords.jaburjak.cz/schema/song-1.xsd"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:noNamespaceSchemaLocation="https://chords.jaburjak.cz/schema/song-1.xsd">
	<info>
		<title>repeats</title>
	</info>
	<lyrics>
		<repeat count="5">
			<strophe label="Y">
				<repeat>
					<paragraph>
						<repeat>
							<verse><repeat count="42"><chord>D</chord>Na</repeat> Okoř je cesta</verse>
						</repeat>
						<verse>jako žádná ze sta,</verse>
					</paragraph>
				</repeat>
			</strophe>
		</repeat>
		<repeat>
			<strophe-ref ref="Y" />
		</repeat>
	</lyrics>
</song>
XML
			,new Song(
				new SongInfo('repeats', null),
				new SongLyrics([
					new Repeat([$Y], 5),
					new Repeat([new StropheReference($Y)], 2)
				])
			)],
			'non-printed chords' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE song PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/song-1.dtd">
<song xmlns="https://chords.jaburjak.cz/schema/song-1.xsd"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:noNamespaceSchemaLocation="https://chords.jaburjak.cz/schema/song-1.xsd">
	<info>
		<title>print</title>
	</info>
	<lyrics>
		<strophe>
			<paragraph>
				<verse><chord print="true">D</chord>Na Okoř je cesta, jako žádná ze sta,</verse>
				<verse><chord print="false">A7</chord>vroubená je stromama<chord>D</chord>.</verse>
			</paragraph>
		</strophe>
	</lyrics>
</song>
XML
			,new Song(
				new SongInfo('print', null),
				new SongLyrics([
					new Strophe([
						new Paragraph([
							new Verse([
								new Chord('D', true), new Text('Na Okoř je cesta, jako žádná ze sta,')
							]),
							new Verse([
								new Chord('A7', false), new Text('vroubená je stromama'), new Chord('D', true), new Text('.')
							])
						])
					], null)
				])
			)],
			'empty lyrics' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE song PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/song-1.dtd">
<song xmlns="https://chords.jaburjak.cz/schema/song-1.xsd"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:noNamespaceSchemaLocation="https://chords.jaburjak.cz/schema/song-1.xsd">
	<info>
		<title>empty</title>
	</info>
	<lyrics />
</song>
XML
			,new Song(
				new SongInfo('empty', null),
				new SongLyrics([])
			)]
		];
	}

	/**
	 * @dataProvider invalidXmlProvider
	 */
	public function testInvalidXml($xml, $expectedException): void {
		$parser = new SongXmlParser();

		$this->expectException($expectedException);

		$parser->parse($xml);
	}

	public function invalidXmlProvider(): array {
		return [
			'missing <info>' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE song PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/song-1.dtd">
<song xmlns="https://chords.jaburjak.cz/schema/song-1.xsd"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:noNamespaceSchemaLocation="https://chords.jaburjak.cz/schema/song-1.xsd">
	<lyrics>
		<strophe>
			<paragraph>
				<verse>dummy</verse>
			</paragraph>
		</strophe>
	</lyrics>
</song>
XML
				,'\Chords\Exception\InvalidXmlException'],
			'missing <title>' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE song PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/song-1.dtd">
<song xmlns="https://chords.jaburjak.cz/schema/song-1.xsd"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:noNamespaceSchemaLocation="https://chords.jaburjak.cz/schema/song-1.xsd">
    <info>
    	<author>author</author>
    </info>
	<lyrics>
		<strophe>
			<paragraph>
				<verse>dummy</verse>
			</paragraph>
		</strophe>
	</lyrics>
</song>
XML
				,'\Chords\Exception\InvalidXmlException'],
			'missing <lyrics>' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE song PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/song-1.dtd">
<song xmlns="https://chords.jaburjak.cz/schema/song-1.xsd"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:noNamespaceSchemaLocation="https://chords.jaburjak.cz/schema/song-1.xsd">
	<info>
		<title>title</title>
	</info>
</song>
XML
				,'\Chords\Exception\InvalidXmlException'],
			'repeat count less than two' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE song PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/song-1.dtd">
<song xmlns="https://chords.jaburjak.cz/schema/song-1.xsd"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:noNamespaceSchemaLocation="https://chords.jaburjak.cz/schema/song-1.xsd">
    <info>
    	<title>title</title>
    </info>
	<lyrics>
		<strophe>
			<paragraph>
				<verse><repeat count="1">repeat</repeat></verse>
			</paragraph>
		</strophe>
	</lyrics>
</song>
XML
				,'\InvalidArgumentException'],
			'undefined strophe reference' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE song PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/song-1.dtd">
<song xmlns="https://chords.jaburjak.cz/schema/song-1.xsd"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:noNamespaceSchemaLocation="https://chords.jaburjak.cz/schema/song-1.xsd">
    <info>
    	<title>title</title>
    </info>
	<lyrics>
		<strophe label="A">
			<paragraph>
				<verse>strophe A</verse>
			</paragraph>
		</strophe>
		<strophe-ref ref="B" />
	</lyrics>
</song>
XML
				,'\Chords\Exception\InvalidXmlException'],
			'non-boolean print attribute' => [<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE song PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/song-1.dtd">
<song xmlns="https://chords.jaburjak.cz/schema/song-1.xsd"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:noNamespaceSchemaLocation="https://chords.jaburjak.cz/schema/song-1.xsd">
    <info>
    	<title>title</title>
    </info>
	<lyrics>
		<strophe>
			<paragraph>
				<verse><chord print="yes">A</chord></verse>
			</paragraph>
		</strophe>
	</lyrics>
</song>
XML
				,'\Chords\Exception\InvalidXmlException']
		];
	}
}