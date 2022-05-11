<?php
/**
 * @author Jakub Jabůrek <jaburek.jakub@gmail.com>
 */

declare(strict_types=1);

namespace Chords\Song\Export;

use InvalidArgumentException;
use UnexpectedValueException;

/**
 * PDF export settings.
 *
 * @package Chords\Song\Export
 */
final class PdfExportOptions {
	/**
	 * Paper size A4.
	 *
	 * @var string
	 */
	public const PAPER_A4 = 'A4';

	/**
	 * Paper size A5.
	 *
	 * @var string
	 */
	public const PAPER_A5 = 'A5';

	/**
	 * Smaller font size.
	 *
	 * @var string
	 */
	public const FONT_SMALLER = 'smaller';

	/**
	 * Regular font size.
	 *
	 * @var string
	 */
	public const FONT_NORMAL = 'normal';

	/**
	 * Larger font size.
	 *
	 * @var string
	 */
	public const FONT_BIGGER = 'bigger';

	/**
	 * Paper size.
	 *
	 * Must be one of the `PAPER_*` constants.
	 *
	 * @var string
	 */
	private $paperSize;

	/**
	 * Number of columns.
	 *
	 * @var int
	 */
	private $columns;

	/**
	 * Font size.
	 *
	 * Must be one of the `FONT_*` constants.
	 *
	 * @var string
	 */
	private $fontSize;

	/**
	 * Should the output include chords marked as hidden for print?
	 *
	 * @var bool
	 */
	private $printHiddenChords;

	/**
	 * Additional metadata to include in the output.
	 *
	 * @var string[]
	 */
	private $metadata;

	/**
	 * @return string paper size
	 */
	public function getPaperSize(): string {
		return $this->paperSize;
	}

	/**
	 * @param string $paperSize paper size
	 * @return PdfExportOptions
	 */
	public function setPaperSize(string $paperSize): PdfExportOptions {
		if (!in_array($paperSize, [self::PAPER_A4, self::PAPER_A5])) {
			throw new UnexpectedValueException('Argument $paperSize must be one of the PdfExportOptions::PAPER_* constants.');
		}

		$this->paperSize = $paperSize;
		return $this;
	}

	/**
	 * @return int number of columns
	 */
	public function getColumns(): int {
		return $this->columns;
	}

	/**
	 * @param int $columns number of columns
	 * @return PdfExportOptions
	 */
	public function setColumns(int $columns): PdfExportOptions {
		if ($columns < 1) {
			throw new UnexpectedValueException('Argument $columns must be at least one.');
		}

		$this->columns = $columns;
		return $this;
	}

	/**
	 * @return string font size
	 */
	public function getFontSize(): string {
		return $this->fontSize;
	}

	/**
	 * @param string $fontSize font size
	 * @return PdfExportOptions
	 */
	public function setFontSize(string $fontSize): PdfExportOptions {
		if (!in_array($fontSize, [self::FONT_SMALLER, self::FONT_NORMAL, self::FONT_BIGGER])) {
			throw new UnexpectedValueException('Argument $fontSize must be one of the PdfExportOptions::FONT_* constants.');
		}

		$this->fontSize = $fontSize;
		return $this;
	}

	/**
	 * @return bool show non-printed chords?
	 */
	public function isPrintHiddenChords(): bool {
		return $this->printHiddenChords;
	}

	/**
	 * @param bool $printHiddenChords show non-printed chords?
	 * @return PdfExportOptions
	 */
	public function setPrintHiddenChords($printHiddenChords): PdfExportOptions {
		$this->printHiddenChords = $printHiddenChords;
		return $this;
	}

	/**
	 * @return string[] additional metadata
	 */
	public function getMetadata(): array {
		return $this->metadata;
	}

	/**
	 * @param string[] $metadata additional metadata
	 * @return PdfExportOptions
	 */
	public function setMetadata(array $metadata): PdfExportOptions {
		array_walk($metadata, function ($item) {
			if (!is_string($item)) {
				throw new InvalidArgumentException(sprintf(
					'Argument $metadata expected to be an array of strings, %s found.',
					gettype($item)
				));
			}
		});

		$this->metadata = array_values($metadata);
		return $this;
	}

	public function __construct() {
		$this->paperSize = self::PAPER_A4;
		$this->columns = 2;
		$this->fontSize = self::FONT_NORMAL;
		$this->printHiddenChords = false;
		$this->metadata = [];
	}
}