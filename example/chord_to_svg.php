<?php
declare(strict_types=1);

use Chords\Chord\Export\ChordSvgExporter;
use Chords\Chord\Parser\ChordXmlParser;

require_once __DIR__.'/vendor/autoload.php';

// read XML file with chord definition

$xml = file_get_contents(__DIR__.'/C.xml');

// parse XML file

$parser = new ChordXmlParser();
$model = $parser->parse($xml);

// export to SVG

$exporter = new ChordSvgExporter();
$svg = $exporter->toSvg($model);

// save the SVG file

file_put_contents(__DIR__.'/C.svg', $svg);