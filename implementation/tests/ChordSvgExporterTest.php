<?php
declare(strict_types=1);

use Chords\Chord\Export\ChordSvgExporter;
use Chords\Chord\Model\Chord;
use Chords\Chord\Model\ChordDefinition;
use Chords\Chord\Model\ChordMark;
use Chords\Chord\Model\ChordNote;
use PHPUnit\Framework\TestCase;

final class ChordSvgExporterTest extends TestCase {
	/**
	 * @dataProvider dataProvider
	 */
	public function testSvgExport($xml, $expected): void {
		$exporter = new ChordSvgExporter();

		$this->assertEquals($expected, $exporter->toSvg($xml, $expected));
	}

	public function dataProvider(): array {
		return [
			[new Chord(
				'Dmi',
				new ChordDefinition(
					6,
					5,
					0,
					[
						new ChordNote([6], 1),
						new ChordNote([4], 2),
						new ChordNote([5], 3)
					],
					[
						new ChordMark(1, ChordMark::TYPE_MUTED),
						new ChordMark(2, ChordMark::TYPE_MUTED),
						new ChordMark(3, ChordMark::TYPE_OPEN)
					]
				)
			), <<<SVG
<?xml version="1.0" encoding="UTF-8"?><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="104" height="135"><title>Dmi</title><line x1="7" y1="17" x2="7" y2="135" style="stroke: #000; stroke-width: 1;" /><line x1="25" y1="17" x2="25" y2="135" style="stroke: #000; stroke-width: 1;" /><line x1="43" y1="17" x2="43" y2="135" style="stroke: #000; stroke-width: 1;" /><line x1="61" y1="17" x2="61" y2="135" style="stroke: #000; stroke-width: 1;" /><line x1="79" y1="17" x2="79" y2="135" style="stroke: #000; stroke-width: 1;" /><line x1="97" y1="17" x2="97" y2="135" style="stroke: #000; stroke-width: 1;" /><line x1="7" y1="25" x2="97" y2="25" style="stroke: #000; stroke-width: 1;" /><line x1="7" y1="45" x2="97" y2="45" style="stroke: #000; stroke-width: 1;" /><line x1="7" y1="65" x2="97" y2="65" style="stroke: #000; stroke-width: 1;" /><line x1="7" y1="85" x2="97" y2="85" style="stroke: #000; stroke-width: 1;" /><line x1="7" y1="105" x2="97" y2="105" style="stroke: #000; stroke-width: 1;" /><line x1="7" y1="125" x2="97" y2="125" style="stroke: #000; stroke-width: 1;" /><rect x="7" y="17" width="90" height="8" style="fill: #000;" /><circle cx="97" cy="35" r="7" style="fill: #000;" /><circle cx="61" cy="55" r="7" style="fill: #000;" /><circle cx="79" cy="75" r="7" style="fill: #000;" /><line x1="1" y1="0" x2="13" y2="12" style="stroke: #000; stroke-width: 1.25;" /><line x1="13" y1="0" x2="1" y2="12" style="stroke: #000; stroke-width: 1.25;" /><line x1="19" y1="0" x2="31" y2="12" style="stroke: #000; stroke-width: 1.25;" /><line x1="31" y1="0" x2="19" y2="12" style="stroke: #000; stroke-width: 1.25;" /><circle cx="43" cy="6" r="6" style="fill: rgba(255, 255, 255, 0); stroke: #000; stroke-width: 1;" /></svg>
SVG
],
			[new Chord(
				'test',
				new ChordDefinition(
					6,
					5,
					2,
					[
						new ChordNote([6], 3),
						new ChordNote([4], 4),
						new ChordNote([5], 5)
					],
					[
						new ChordMark(1, ChordMark::TYPE_MUTED),
						new ChordMark(2, ChordMark::TYPE_MUTED),
						new ChordMark(3, ChordMark::TYPE_OPEN)
					]
				)
			), <<<SVG
<?xml version="1.0" encoding="UTF-8"?><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="127" height="95"><title>test</title><line x1="7" y1="17" x2="7" y2="95" style="stroke: #000; stroke-width: 1;" /><line x1="25" y1="17" x2="25" y2="95" style="stroke: #000; stroke-width: 1;" /><line x1="43" y1="17" x2="43" y2="95" style="stroke: #000; stroke-width: 1;" /><line x1="61" y1="17" x2="61" y2="95" style="stroke: #000; stroke-width: 1;" /><line x1="79" y1="17" x2="79" y2="95" style="stroke: #000; stroke-width: 1;" /><line x1="97" y1="17" x2="97" y2="95" style="stroke: #000; stroke-width: 1;" /><line x1="7" y1="25" x2="97" y2="25" style="stroke: #000; stroke-width: 1;" /><line x1="7" y1="45" x2="97" y2="45" style="stroke: #000; stroke-width: 1;" /><line x1="7" y1="65" x2="97" y2="65" style="stroke: #000; stroke-width: 1;" /><line x1="7" y1="85" x2="97" y2="85" style="stroke: #000; stroke-width: 1;" /><text x="107" y="34" fill="#000;" font-family="sans-serif" font-size="1em">iii</text><circle cx="97" cy="35" r="7" style="fill: #000;" /><circle cx="61" cy="55" r="7" style="fill: #000;" /><circle cx="79" cy="75" r="7" style="fill: #000;" /><line x1="1" y1="0" x2="13" y2="12" style="stroke: #000; stroke-width: 1.25;" /><line x1="13" y1="0" x2="1" y2="12" style="stroke: #000; stroke-width: 1.25;" /><line x1="19" y1="0" x2="31" y2="12" style="stroke: #000; stroke-width: 1.25;" /><line x1="31" y1="0" x2="19" y2="12" style="stroke: #000; stroke-width: 1.25;" /><circle cx="43" cy="6" r="6" style="fill: rgba(255, 255, 255, 0); stroke: #000; stroke-width: 1;" /></svg>
SVG
],
			[new Chord(
				'C',
				new ChordDefinition(
					6,
					12,
					7,
					[
						new ChordNote([6], 8),
						new ChordNote([5], 8),
						new ChordNote([4], 9),
						new ChordNote([3], 10)
					],
					[
						new ChordMark(1, ChordMark::TYPE_MUTED),
						new ChordMark(2, ChordMark::TYPE_MUTED)
					]
				),
				[
					'Cdur',
					'Cmajor'
				]
			), <<<SVG
<?xml version="1.0" encoding="UTF-8"?><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="127" height="135"><title>C</title><line x1="7" y1="17" x2="7" y2="135" style="stroke: #000; stroke-width: 1;" /><line x1="25" y1="17" x2="25" y2="135" style="stroke: #000; stroke-width: 1;" /><line x1="43" y1="17" x2="43" y2="135" style="stroke: #000; stroke-width: 1;" /><line x1="61" y1="17" x2="61" y2="135" style="stroke: #000; stroke-width: 1;" /><line x1="79" y1="17" x2="79" y2="135" style="stroke: #000; stroke-width: 1;" /><line x1="97" y1="17" x2="97" y2="135" style="stroke: #000; stroke-width: 1;" /><line x1="7" y1="25" x2="97" y2="25" style="stroke: #000; stroke-width: 1;" /><line x1="7" y1="45" x2="97" y2="45" style="stroke: #000; stroke-width: 1;" /><line x1="7" y1="65" x2="97" y2="65" style="stroke: #000; stroke-width: 1;" /><line x1="7" y1="85" x2="97" y2="85" style="stroke: #000; stroke-width: 1;" /><line x1="7" y1="105" x2="97" y2="105" style="stroke: #000; stroke-width: 1;" /><line x1="7" y1="125" x2="97" y2="125" style="stroke: #000; stroke-width: 1;" /><text x="107" y="34" fill="#000;" font-family="sans-serif" font-size="1em">viii</text><circle cx="97" cy="35" r="7" style="fill: #000;" /><circle cx="79" cy="35" r="7" style="fill: #000;" /><circle cx="61" cy="55" r="7" style="fill: #000;" /><circle cx="43" cy="75" r="7" style="fill: #000;" /><line x1="1" y1="0" x2="13" y2="12" style="stroke: #000; stroke-width: 1.25;" /><line x1="13" y1="0" x2="1" y2="12" style="stroke: #000; stroke-width: 1.25;" /><line x1="19" y1="0" x2="31" y2="12" style="stroke: #000; stroke-width: 1.25;" /><line x1="31" y1="0" x2="19" y2="12" style="stroke: #000; stroke-width: 1.25;" /></svg>
SVG
],
			[new Chord(
				'F',
				new ChordDefinition(
					6,
					5,
					0,
					[
						new ChordNote([1, 6], 1),
						new ChordNote([4], 2),
						new ChordNote([2], 3),
						new ChordNote([3], 3)
					],
					[]
				),
				[]
			), <<<SVG
<?xml version="1.0" encoding="UTF-8"?><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="104" height="118"><title>F</title><line x1="7" y1="0" x2="7" y2="118" style="stroke: #000; stroke-width: 1;" /><line x1="25" y1="0" x2="25" y2="118" style="stroke: #000; stroke-width: 1;" /><line x1="43" y1="0" x2="43" y2="118" style="stroke: #000; stroke-width: 1;" /><line x1="61" y1="0" x2="61" y2="118" style="stroke: #000; stroke-width: 1;" /><line x1="79" y1="0" x2="79" y2="118" style="stroke: #000; stroke-width: 1;" /><line x1="97" y1="0" x2="97" y2="118" style="stroke: #000; stroke-width: 1;" /><line x1="7" y1="8" x2="97" y2="8" style="stroke: #000; stroke-width: 1;" /><line x1="7" y1="28" x2="97" y2="28" style="stroke: #000; stroke-width: 1;" /><line x1="7" y1="48" x2="97" y2="48" style="stroke: #000; stroke-width: 1;" /><line x1="7" y1="68" x2="97" y2="68" style="stroke: #000; stroke-width: 1;" /><line x1="7" y1="88" x2="97" y2="88" style="stroke: #000; stroke-width: 1;" /><line x1="7" y1="108" x2="97" y2="108" style="stroke: #000; stroke-width: 1;" /><rect x="7" y="0" width="90" height="8" style="fill: #000;" /><rect x="0" y="13" width="104" height="10" rx="6" style="fill: #000;" /><circle cx="61" cy="38" r="7" style="fill: #000;" /><circle cx="25" cy="58" r="7" style="fill: #000;" /><circle cx="43" cy="58" r="7" style="fill: #000;" /></svg>
SVG
]
		];
	}
}