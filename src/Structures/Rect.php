<?php

namespace GD\Structures;


class Rect {
    /** @var Point $location */
    public $location;

    /** @var Size $size */
    public $size;

    public function __construct() {
        $this->location = new Point();
        $this->size = new Size();
    }

    public static function of($location, $size) {
        $result = new Rect();
        $result->location = $location;
        $result->size = $size;
        return $result;
    }

    public static function ofScalars($x, $y, $width, $height) {
        return self::of(Point::of($x, $y), Size::of($width, $height));
    }

    public function toArray() {
        return array_merge($this->location->toArray(), $this->size->toArray());
    }

    public function leftTop() {
        return $this->location;
    }

    public function leftBottom() {
        return Point::of($this->location->x, $this->location->y + $this->size->height);
    }

    public function rightTop() {
        return Point::of($this->location->x + $this->size->width, $this->location->y);
    }

    public function rightBottom() {
        return Point::of($this->location->x + $this->size->width, $this->location->y + $this->size->height);
    }
}
