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

    public function handle() {
        return $this->handle;
    }

    public function filename() {
        return $this->filename;
    }

    public function size() {
        return Size::of(imagefontwidth($this->handle), imagefontheight($this->handle));
    }

    public static function computeTextBox(string $text, string $fontFilename, float $fontSize, float $angle = 0) {
        $box = imagettfbbox($fontSize, $angle, $fontFilename, $text);
        $location = Point::of($box[6], $box[7]);
        $size = Size::of($box[2] - $location->x, $box[3] - $location->y);
        return Rect::of($location, $size);
    }
}
