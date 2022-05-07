<?php
declare(strict_types=1);

namespace Chords\Song\Export;

use \RuntimeException;
use \UnexpectedValueException;
use Chords\Song\Model\SongInfo;
use Chords\Song\Model\SongLyrics;
use Chords\Song\Model\Song;
use Dompdf\Dompdf;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

final class SongPdfExporter implements SongPdfExporterInterface {
	private const COLUMN_SPACE = 10;

	private const HEADER_HEIGHT = 80;

	private const MARGIN_TOP = 20;

	private const MARGIN_BOTTOM = 20;

	private const MARGIN_LEFT = 40;

	private const MARGIN_RIGHT = 40;

	private const TEMP_EXCEPTION_MESSAGE = 'Could not save temporary PDF file.';

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
		$columnHeight = $paper[3] - self::MARGIN_TOP - self::HEADER_HEIGHT - self::MARGIN_BOTTOM;

		$text = $this->buildText($song->getLyrics(), $options, $columnWidth, $columnHeight);
		$title = $this->buildTitle($song->getInfo(), $options, $paper[2] - self::MARGIN_LEFT - self::MARGIN_RIGHT);
		$header = null;

		$pdf = new Fpdi('P', 'pt');
		$pdf->SetMargins(self::MARGIN_LEFT, self::MARGIN_TOP, self::MARGIN_RIGHT);

		$pageCount = $pdf->setSourceFile($text);

		if ($pageCount > $columns) {
			$header = $this->buildHeader($song->getInfo(), $options, $paper[2] - self::MARGIN_LEFT - self::MARGIN_RIGHT);
		}

		for ($i = 1; $i <= $pageCount; $i++) {
			$x = (($i - 1) % $columns) * $columnWidth;
			$x += self::MARGIN_LEFT;

			if (($i - 1) % $columns === 0) {
				$pdf->AddPage('P', [$paper[2], $paper[3]]);

				if ($i === 1) {
					$pdf->setSourceFile($title);
				} else {
					$pdf->setSourceFile($header);
				}

				$pdf->useImportedPage(
					$pdf->importPage(1, PdfReader\PageBoundaries::MEDIA_BOX),
					self::MARGIN_LEFT,
					self::MARGIN_TOP
				);

				$pdf->setSourceFile($text);
			} else {
				$x += self::COLUMN_SPACE;
			}

			$pageId = $pdf->importPage($i, PdfReader\PageBoundaries::MEDIA_BOX);
			$pdf->useImportedPage($pageId, $x, self::MARGIN_TOP + self::HEADER_HEIGHT);
		}

		unlink($text);
		unlink($title);

		if ($header !== null) {
			unlink($header);
		}

		$pdf->setTitle($song->getInfo()->getTitle(), true);

		return $pdf->Output('S');
	}

	private function buildText(SongLyrics $lyrics, PdfExportOptions $options, float $columnWidth,
	                           float $columnHeight): string {
		$html = <<<HTML
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<style>
			html {
				margin: 0.25em 0;
			}

			body {
				font-family: DejaVu Serif;
				font-size: __FONT_SIZE__;
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

		$html = str_replace('__FONT_SIZE__', $this->fontSizeToPoints($options->getFontSize()), $html);

		$visitor = new PdfExportVisitor($options);
		$lyrics->accept($visitor);

		$html .= $visitor->saveHtml();
		$html .= '</body></html>';

		$file = $this->getTempFile();

		$dompdf = new Dompdf();
		$dompdf->loadHtml($html);
		$dompdf->setPaper([0, 0, $columnWidth, $columnHeight], 'portrait');
		$dompdf->render();
		
		if (file_put_contents($file, $dompdf->output()) === false) {
			throw new RuntimeException(self::TEMP_EXCEPTION_MESSAGE);
		}

		return $file;
	}

	private function buildTitle(SongInfo $info, PdfExportOptions $options, float $pageWidth): string {
		$html = <<<HTML
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<style>
			html {
				margin: 0.25em 0;
			}

			body {
				font-family: DejaVu Serif;
				font-size: __FONT_SIZE__;
				text-align: center;
			}

			h1 {
				margin: 0;
			}

			.author, .meta {
				font-style: italic;
			}
		</style>
	</head>
	<body>
HTML
;

		$html = str_replace('__FONT_SIZE__', $this->fontSizeToPoints($options->getFontSize()), $html);

		$visitor = new PdfExportVisitor($options);
		$info->accept($visitor);

		$html .= $visitor->saveHtml();
		$html .= '</body></html>';

		$file = $this->getTempFile();

		$dompdf = new Dompdf();
		$dompdf->loadHtml($html);
		$dompdf->setPaper([0, 0, $pageWidth, self::HEADER_HEIGHT], 'portrait');
		$dompdf->render();
		
		if (file_put_contents($file, $dompdf->output()) === false) {
			throw new RuntimeException(self::TEMP_EXCEPTION_MESSAGE);
		}

		return $file;
	}

		private function buildHeader(SongInfo $info, PdfExportOptions $options, float $pageWidth): string {
		$html = <<<HTML
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<style>
			html {
				margin: 0.25em 0;
			}

			body {
				font-family: DejaVu Serif;
				font-size: __FONT_SIZE__;
				text-align: center;
			}

			h1 {
				margin: 0;
				display: inline;
				font-size: 1em;
			}

			.author {
				display: inline;
				font-style: italic;
			}

			.author::before {
				content: " — ";
			}
		</style>
	</head>
	<body>
HTML
;

		$html = str_replace('__FONT_SIZE__', $this->fontSizeToPoints($options->getFontSize()), $html);

		$meta = $options->getMetadata();
		$options->setMetadata([]);

		$visitor = new PdfExportVisitor($options);
		$info->accept($visitor);

		$html .= $visitor->saveHtml();
		$html .= '</body></html>';

		$options->setMetadata($meta);

		$file = $this->getTempFile();

		$dompdf = new Dompdf();
		$dompdf->loadHtml($html);
		$dompdf->setPaper([0, 0, $pageWidth, self::HEADER_HEIGHT], 'portrait');
		$dompdf->render();
		
		if (file_put_contents($file, $dompdf->output()) === false) {
			throw new RuntimeException(self::TEMP_EXCEPTION_MESSAGE);
		}

		return $file;
	}

	private function fontSizeToPoints(string $fontSize): string {
		switch ($fontSize) {
			case PdfExportOptions::FONT_SMALLER:
				return '8pt';
			case PdfExportOptions::FONT_NORMAL:
				return '10pt';
			case PdfExportOptions::FONT_BIGGER:
				return '12pt';
			default:
				throw new UnexpectedValueException(sprintf('Unsupported font size "%s".', $options->getFontSize()));
		}
	}

	private function getTempFile(): string {
		return sprintf('%s/%s.pdf', sys_get_temp_dir(), md5(microtime(true).strval(rand())));
	}
}