<?php
declare(strict_types=1);

namespace Chords\Song\Export;

use \UnexpectedValueException;

final class PdfExportOptions {
	public const PAPER_A4 = 'A4';

	public const PAPER_A5 = 'A5';

	public const FONT_SMALLER = 'smaller';

	public const FONT_NORMAL = 'normal';

	public const FONT_BIGGER = 'bigger';

	/**
	 * @var string
	 */
	private $paperSize;

	/**
	 * @var int
	 */
	private $columns;

	/**
	 * @var string
	 */
	private $fontSize;

	/**
	 * @var bool
	 */
	private $printHiddenChords;

	public function getPaperSize(): string {
		return $this->paperSize;
	}

	public function setPaperSize(string $paperSize): PdfExportOptions {
		if (!in_array($paperSize, [self::PAPER_A4, self::PAPER_A5])) {
			throw new UnexpectedValueException('Argument $paperSize must be one of the PdfExportOptions::PAPER_* constants.');
		}

		$this->paperSize = $paperSize;
		return $this;
	}

	public function getColumns(): int {
		return $this->columns;
	}

	public function setColumns(int $columns): PdfExportOptions {
		if ($columns < 1) {
			throw new UnexpectedValueException('Argument $columns must be at least one.');
		}

		$this->columns = $columns;
		return $this;
	}

	public function getFontSize(): string {
		return $this->fontSize;
	}

	public function setFontSize(string $fontSize): PdfExportOptions {
		if (!in_array($fontSize, [self::FONT_SMALLER, self::FONT_NORMAL, self::FONT_BIGGER])) {
			throw new UnexpectedValueException('Argument $fontSize must be one of the PdfExportOptions::FONT_* constants.');
		}

		$this->fontSize = $fontSize;
		return $this;
	}

	public function isPrintHiddenChords(): bool {
		return $this->printHiddenChords;
	}

	public function setPrintHiddenChords($printHiddenChords): PdfExportOptions {
		$this->printHiddenChords = $printHiddenChords;
		return $this;
	}

	public function __construct() {
		$this->paperSize = self::PAPER_A4;
		$this->columns = 2;
		$this->fontSize = self::FONT_NORMAL;
		$this->printHiddenChords = false;
	}
}