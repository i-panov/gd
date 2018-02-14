<?php

namespace GD;

use GD\Structures\Rect;
use GD\Structures\Size;
use GD\Structures\Point;

class Font {
    /** @var resource $handle */
    private $handle;

    /** @var string $filename */
    private $filename;

    /**
     * @param string $filename
     */
    public function __construct($filename) {
        $this->handle = imageloadfont($filename);
        $this->filename = $filename;
    }

    public function getHandle() {
        return $this->handle;
    }

    public function getFilename() {
        return $this->filename;
    }

    public function getSize() {
        return Size::of(imagefontwidth($this->handle), imagefontheight($this->handle));
    }

    /**
     * @param string $text
     * @param string $fontFilename
     * @param float $fontSize
     * @param int $angle
     * @return Rect
     */
    public static function computeTextBox($text, $fontFilename, $fontSize, $angle = 0) {
        $box = imagettfbbox($fontSize, $angle, $fontFilename, $text);
        $location = Point::of($box[6], $box[7]);
        $size = Size::of($box[2] - $location->x, $box[3] - $location->y);
        return Rect::of($location, $size);
    }
}
