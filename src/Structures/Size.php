<?php

namespace GD\Structures;


class Size {
    /** @var int $width, $height */
    public $width = 0, $height = 0;

    public static function of($width, $height) {
        $result = new Size();
        $result->width = $width;
        $result->height = $height;
        return $result;
    }

    public function toArray() {
        return [$this->width, $this->height];
    }
}
