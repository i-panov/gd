<?php

namespace GD\Structures;


class Point {
    /** @var int $x, $y */
    public $x = 0, $y = 0;

    public static function of($x, $y) {
        $result = new Point();
        $result->x = $x;
        $result->y = $y;
        return $result;
    }

    public function toArray() {
        return [$this->x, $this->y];
    }
}
