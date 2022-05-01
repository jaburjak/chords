<?php
declare(strict_types=1);

namespace Chords\Song\Export;

use \RuntimeException;
use \UnexpectedValueException;
use Chords\Song\Model\Song;
use Dompdf\Dompdf;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

final class SongPdfExporter implements SongPdfExporterInterface {
	private const COLUMN_SPACE = 10;

	private const MARGIN_TOP = 40;

	private const MARGIN_BOTTOM = 40;

	private const MARGIN_LEFT = 40;

	private const MARGIN_RIGHT = 40;

	public function toPdf(Song $song, PdfExportOptions $options): string {
		switch ($options->getPaperSize()) {
			case PdfExportOptions::PAPER_A4:
				$paper = [0, 0, 595.28, 841.89];
				break;
			case PdfExportOptions::PAPER_A5:
				$paper = [0, 0, 419.53, 595.28];
				break;
			default:
				throw new UnexpectedValueException(sprintf('Unknown paper size: "%s".', $options->getPaperSize()));
		}

		$columns = $options->getColumns();

		if ($columns < 1 or $columns > 4) {
			throw new UnexpectedValueException('Option "columns" must be greater than zero and less than five.');
		}

		$columnWidth = ($paper[2] - self::MARGIN_LEFT - self::MARGIN_RIGHT) / $columns - self::COLUMN_SPACE / 2;
		$columnHeight = $paper[3] - self::MARGIN_TOP - self::MARGIN_BOTTOM;

		$tmpFile = sprintf('%s/%s.pdf', sys_get_temp_dir(), md5(microtime(true).strval(rand())));

		$dompdf = new Dompdf();
		$dompdf->loadHtml($this->buildHtml($song, $options));
		$dompdf->setPaper([0, 0, $columnWidth, $columnHeight], 'portrait');
		$dompdf->render();
		
		if (file_put_contents($tmpFile, $dompdf->output()) === false) {
			throw new RuntimeException('Could not save temporary PDF file.');
		}

		$pdf = new Fpdi('P', 'pt');
		$pdf->SetMargins(self::MARGIN_LEFT, self::MARGIN_TOP, self::MARGIN_RIGHT);

		$pageCount = $pdf->setSourceFile($tmpFile);

		for ($i = 1; $i <= $pageCount; $i++) {
			$pageId = $pdf->importPage($i, PdfReader\PageBoundaries::MEDIA_BOX);

			$x = (($i - 1) % $columns) * $columnWidth;
			$x += self::MARGIN_LEFT;

			if (($i - 1) % $columns === 0) {
				$pdf->AddPage('P', [$paper[2], $paper[3]]);
			} else {
				$x += self::COLUMN_SPACE;
			}

			$pdf->useImportedPage($pageId, $x, self::MARGIN_TOP);
		}

		unlink($tmpFile);

		$pdf->setTitle($song->getInfo()->getTitle(), true);

		return $pdf->Output('S');
	}

	private function buildHtml(Song $song, PdfExportOptions $options): string {
		$html = <<<HTML
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<style>
			html {
				margin: 0.5em 0.25em;
			}

			body {
				font-family: DejaVu Serif;
				font-size: __FONT_SIZE__;
			}

			.header {
				margin-bottom: 3em;
			}

			h1 {
				margin: 0;
			}

			.author {
				font-style: italic;
			}

			.paragraph {
				margin-bottom: 2em;
			}

			.cell {
				display: inline-block;
			}

			.chord {
				color: #3C763D;
				font-weight: bold;
			}

			.strophe-label, .strophe-reference {
				font-weight: bold;
			}
		</style>
	</head>
	<body>
HTML
;

		$fontSize = '10pt';

		switch ($options->getFontSize()) {
			case PdfExportOptions::FONT_SMALLER:
				$fontSize = '8pt';
				break;
			case PdfExportOptions::FONT_NORMAL:
				$fontSize = '10pt';
				break;
			case PdfExportOptions::FONT_BIGGER:
				$fontSize = '12pt';
				break;
			default:
				throw new UnexpectedValueException(sprintf('Unsupported font size "%s".', $options->getFontSize()));
		}

		$html = str_replace('__FONT_SIZE__', $fontSize, $html);

		$visitor = new PdfExportVisitor($options);

		$song->accept($visitor);

		$html .= $visitor->saveHtml();

		$html .= '</body></html>';

		return $html;
	}
}