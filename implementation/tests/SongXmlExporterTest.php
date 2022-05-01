<?php
declare(strict_types=1);

use Chords\Song\Export\SongXmlExporter;
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
use PHPUnit\Framework\TestCase;

final class SongXmlExporterTest extends TestCase {
	/**
	 * @dataProvider dataProvider
	 */
	public function testXmlExport($song, $expected): void {
		$exporter = new SongXmlExporter();

		$xml = $exporter->toXml($song);

		$this->assertEquals($expected, $xml);
	}

	public function dataProvider(): array {
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

		return [
			[
				new Song(
					new SongInfo('Okoř', null),
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
				),
				<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE song PUBLIC "-//JABURJAK//DTD Chord 1.0//EN" "https://chords.jaburjak.cz/dtd/song-1.dtd">
<song xmlns="https://chords.jaburjak.cz/schema/song-1.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="https://chords.jaburjak.cz/schema/song-1.xsd">
  <info>
    <title>Okoř</title>
  </info>
  <lyrics>
    <strophe>
      <paragraph>
        <verse><chord>D</chord>Na Okoř je cesta jako žádná ze sta,</verse>
        <verse><chord>A7</chord>vroubená je <repeat count="42">stromama</repeat><chord>D</chord>.</verse>
      </paragraph>
    </strophe>
    <strophe label="R">
      <repeat>
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
    <strophe-ref ref="R"/>
  </lyrics>
</song>

XML
			]
		];
	}
}