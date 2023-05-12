<?php
declare(strict_types=1);

use Chords\Song\Export\PdfExportOptions;
use Chords\Song\Export\SongPdfExporter;
use Chords\Song\Parser\SongXmlParser;

require_once __DIR__.'/vendor/autoload.php';

// read XML file with song lyrics

$xml = file_get_contents(__DIR__.'/okor.xml');

// parse XML file

$parser = new SongXmlParser();
$model = $parser->parse($xml);

// configure export options

$options = new PdfExportOptions();
$options->setPaperSize(PdfExportOptions::PAPER_A4);
$options->setMetadata([
	'(trampská píseň)',
	'k táboráku'
]);

// export to PDF

$exporter = new SongPdfExporter();
$pdf = $exporter->toPdf($model, $options);

// save the PDF file

file_put_contents(__DIR__.'/okor.pdf', $pdf);