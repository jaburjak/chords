<?php
declare(strict_types=1);

namespace Chords\Chord\Export;

use UnexpectedValueException;
use Chords\Chord\Model\Chord;
use Chords\Chord\Model\ChordMark;

final class ChordSvgExporter implements ChordSvgExporterInterface {
	/**
	 * @var int
	 */
	private $headerHeight = 16;

	/**
	 * @var int
	 */
	private $spacingX = 20;

	/**
	 * @var int
	 */
	private $spacingY = 20;

	private $cellSize = [
		'width' => 18,
		'height' => 20
	];

	private $cellOverflow = [
		'top' => 8,
		'bottom' => 10
	];

	private $noteRadius = 7;

	private $offsetIndicatorSize = [
		'width' => 20,
		'height' => 18
	];

	private $offsetIndicatorMargin = 10;

	private $markSize = [
		'width' => 12,
		'height' => 12
	];

	private $markMargin = 5;

	/**
	 * @inheritdoc
	 */
	public function toSvg(Chord $chord): string {
		$svg = '<?xml version="1.0" encoding="UTF-8"?>';

		$width = ($chord->getDefinition()->getStrings() - 1) * $this->cellSize['width'] +
		         $this->noteRadius;

		if ($chord->getDefinition()->getFretOffset() > 0) {
			$width += $this->offsetIndicatorMargin + $this->offsetIndicatorSize['width'];
		} else {
			$width += $this->noteRadius;
		}

		$height = ($chord->getDefinition()->getFrets() - $chord->getDefinition()->getFretOffset()) * $this->cellSize['height'] +
		          $this->cellOverflow['top'] + $this->cellOverflow['bottom'];

		if (count($chord->getDefinition()->getMarks()) > 0) {
			$height += $this->markSize['height'] + $this->markMargin;
		}

		$svg .= sprintf(
			'<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="%d" height="%d">',
			$width,
			$height
		);

		$svg .= sprintf('<title>%s</title>', htmlspecialchars($chord->getName()));

		$cellsOffset = [
			'x' => $this->noteRadius,
			'y' => (count($chord->getDefinition()->getMarks()) > 0 ? ($this->markSize['height'] + $this->markMargin) : 0) +
			       $this->cellOverflow['top']
		];

		for ($i = 0; $i < $chord->getDefinition()->getStrings(); $i++) {
			$x = $this->cellSize['width'] * $i + $cellsOffset['x'];

			$svg .= sprintf(
				'<line x1="%d" y1="%d" x2="%d" y2="%d" style="stroke: #000; stroke-width: 1;" />',
				$x,
				$cellsOffset['y'] - $this->cellOverflow['top'],
				$x,
				$cellsOffset['y'] +
					($chord->getDefinition()->getFrets() - $chord->getDefinition()->getFretOffset()) * $this->cellSize['height'] +
					$this->cellOverflow['bottom']
			);
		}

		for ($i = 0; $i <= $chord->getDefinition()->getFrets() - $chord->getDefinition()->getFretOffset(); $i++) {
			$y = $this->cellSize['height'] * $i + $cellsOffset['y'];

			$svg .= sprintf(
				'<line x1="%d" y1="%d" x2="%d" y2="%d" style="stroke: #000; stroke-width: 1;" />',
				$cellsOffset['x'],
				$y,
				$cellsOffset['x'] + ($chord->getDefinition()->getStrings() - 1) * $this->cellSize['width'],
				$y
			);
		}

		if ($chord->getDefinition()->getFretOffset() === 0) {
			$svg .= sprintf(
				'<rect x="%d" y="%d" width="%d" height="%d" style="fill: #000;" />',
				$cellsOffset['x'],
				$cellsOffset['y'] - $this->cellOverflow['top'],
				($chord->getDefinition()->getStrings() - 1) * $this->cellSize['width'],
				$this->cellOverflow['top']
			);
		} else {
			$svg .= sprintf(
				'<text x="%d" y="%d" fill="#000;" font-family="sans-serif" font-size="1em">%s</text>',
				$cellsOffset['x'] + ($chord->getDefinition()->getStrings() - 1) * $this->cellSize['width'] + $this->offsetIndicatorMargin,
				$cellsOffset['y'] + round($this->offsetIndicatorSize['height'] / 2),
				self::toRoman($chord->getDefinition()->getFretOffset() + 1)
			);
		}

		foreach ($chord->getDefinition()->getNotes() as $note) {
			$svg .= sprintf(
				'<circle cx="%d" cy="%d" r="%d" style="fill: #000;" />',
				$cellsOffset['x'] + ($note->getString() - 1) * $this->cellSize['width'],
				$cellsOffset['y'] +
					($note->getFret() - $chord->getDefinition()->getFretOffset() - 1) * $this->cellSize['height'] +
					round($this->cellSize['height'] / 2),
				$this->noteRadius
			);
		}

		foreach ($chord->getDefinition()->getMarks() as $mark) {
			switch ($mark->getType()) {
				case ChordMark::TYPE_OPEN:
					$svg .= sprintf(
						'<circle cx="%d" cy="%d" r="%d" style="fill: rgba(255, 255, 255, 0); stroke: #000; stroke-width: 1;" />',
						$cellsOffset['x'] + ($mark->getString() - 1) * $this->cellSize['width'],
						round($this->markSize['height'] / 2),
						round($this->markSize['width'] / 2)
					);
					break;
				case ChordMark::TYPE_MUTED:
					$x = [
						$cellsOffset['x'] - round($this->markSize['width'] / 2) + ($mark->getString() - 1) * $this->cellSize['width'],
						$cellsOffset['x'] + round($this->markSize['width'] / 2) + ($mark->getString() - 1) * $this->cellSize['width']
					];
					$y = [
						0,
						$this->markSize['height']
					];
					$svg .= sprintf(
						'<line x1="%d" y1="%d" x2="%d" y2="%d" style="stroke: #000; stroke-width: 1.25;" /><line x1="%d" y1="%d" x2="%d" y2="%d" style="stroke: #000; stroke-width: 1.25;" />',
						$x[0],
						$y[0],
						$x[1],
						$y[1],
						$x[1],
						$y[0],
						$x[0],
						$y[1]
					);
					break;
				default:
					throw new UnexpectedValueException(sprintf('Unsupported mark type: "%s".', $mark->getType()));
			}
		}

		$svg .= '</svg>';

		return $svg;
	}

	private static function toRoman(int $integer): string {
		$roman = '';

		$lookup = [
			'm' => 1000,
			'cm' => 900,
			'd' => 500,
			'cd' => 400,
			'c' => 100,
			'xc' => 90,
			'l' => 50,
			'xl' => 40,
			'x' => 10,
			'ix' => 9,
			'v' => 5,
			'iv' => 4,
			'i' => 1
		];

		foreach ($lookup as $rm => $value) {
			$roman .= str_repeat($rm, (integer) ($integer / $value));

			$integer = $integer % $value;
		}

		return $roman;
	}
}