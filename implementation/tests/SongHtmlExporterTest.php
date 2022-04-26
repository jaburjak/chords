<?php
declare(strict_types=1);

use Chords\Song\Export\SongHtmlExporter;
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

final class SongHtmlExporterTest extends TestCase {
	/**
	 * @dataProvider dataProvider
	 */
	public function testHtmlExport($song, $expected): void {
		$exporter = new SongHtmlExporter();

		$html = $exporter->toHtml($song);

		$this->assertEquals($expected, $html);
	}

	public function dataProvider(): array {
		$R = new Strophe([
				new Paragraph([
					new Verse([
						new Text('Na hradě Okoři '),
						new Chord('A7'),
						new Text('světla už nehoří,')
					]),
				]),
				new Paragraph([
					new Repeat([
						new Verse([
							new Chord('D'),
							new Text('on jí sebral '),
							new Chord('A7'),
							new Text('od komnaty klíč.')
						])
					], 2)
				])
		], 'R');

		return [
			[
				new Song(
					new SongInfo('Okoř', null),
					new SongLyrics([
						new Strophe([
							new Paragraph([
								new Verse([
									new Chord('D'),
									new Text('Na Okoř je cesta jako žádná ze sta,')
								]),
								new Verse([
									new Chord('A7'),
									new Text('vroubená je '),
									new Repeat([
										new Text('stromama')
									], 42),
									new Chord('D'),
									new Text('.')
								])
							])
						], null),
						new Repeat([$R], 2),
						new Strophe([
							new Paragraph([
								new Verse([
									new Chord('D'),
									new Text('Jednoho dne z rána,')
								])
							])
						], '2'),
						new Repeat(
							[new StropheReference($R)],
							2
						)
					])
				),
				<<<HTML
<div class="song-paragraph"><table><tbody><tr><td class="chord">D</td></tr><tr><td>Na Okoř je cesta jako žádná ze sta,</td></tr></tbody></table><table><tbody><tr><td class="chord">A7</td><td class="chord"></td><td class="chord"></td><td class="chord"></td><td class="chord">D</td></tr><tr><td>vroubená je </td><td class="repeat-marker">[: </td><td>stromama</td><td class="repeat-marker"> :] 42×</td><td>.</td></tr></tbody></table></div><div class="song-paragraph"><table><tbody><tr><td class="chord"></td><td class="chord"></td><td class="chord">A7</td></tr><tr><td class="strophe-label">R (2×). </td><td>Na hradě Okoři </td><td>světla už nehoří,</td></tr></tbody></table></div><div class="song-paragraph"><table><tbody><tr><td class="chord"></td><td class="chord">D</td><td class="chord">A7</td><td class="chord"></td></tr><tr><td class="repeat-marker">[: </td><td>on jí sebral </td><td>od komnaty klíč.</td><td class="repeat-marker"> :]</td></tr></tbody></table></div><div class="song-paragraph"><table><tbody><tr><td class="chord"></td><td class="chord">D</td></tr><tr><td class="strophe-label">2. </td><td>Jednoho dne z rána,</td></tr></tbody></table></div><div class="song-paragraph"><table><tbody><tr><td class="chord"></td></tr><tr><td class="strophe-reference">R. 2×</td></tr></tbody></table></div>
HTML
			]
		];
	}
}