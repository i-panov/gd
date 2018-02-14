<?php

namespace GD\Widgets;

use GD\Image;
use GD\Color;
use GD\Font;
use GD\Structures\Rect;

class TextBox {
    /** @var Image */
    private $img;

    /** @var string */
    private $fontFilename;

    /** @var int */
    private $fontSize;

    /** @var Rect */
    private $rect;

    /** @var float */
    private $angle;

    /** @var Color */
    private $color;

    private function __construct() {
    }

    /**
     * @param Image $img
     * @return self
     */
    public static function of($img) {
        $result = new self();
        $result->img = $img;
        return $result;
    }

    /**
     * @param string $filename
     * @param int $size
     * @return $this
     */
    public function font($filename, $size) {
        $this->fontFilename = $filename;
        $this->fontSize = $size;
        return $this;
    }

    /**
     * @param Rect $value
     * @return $this
     */
    public function rect($value) {
        $this->rect = $value;
        return $this;
    }

    /**
     * @param float $value
     * @return $this
     */
    public function angle($value) {
        $this->angle = $value;
        return $this;
    }

    /**
     * @param Color $value
     * @return $this
     */
    public function color($value) {
        $this->color = $value;
        return $this;
    }

    /**
     * @param string $text
     * @return bool
     */
    public function draw($text) {
        $computedBox = Font::computeTextBox($text, $this->fontFilename, $this->fontSize, $this->angle);
        list($computedBoxWidth, $requiredBoxWidth) = [$computedBox->size->width, $this->rect->size->width];

        if ($computedBoxWidth > $requiredBoxWidth) {
            $lineLength = (int)round($computedBoxWidth / $requiredBoxWidth * 2.4);
            $text = wordwrap($text, $lineLength, "\n", true);
        }

        return $this->img->drawText(
            $text,
            $this->fontFilename,
            $this->fontSize,
            $this->color,
            $this->rect->location,
            $this->angle
        );
    }
}
