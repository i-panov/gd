<?php

namespace GD;


class Color {
    /** @var int $value */
    public $value;

    public function __construct($value = 0) {
        $this->value = $value;
    }

    public static function of($r, $g, $b, $a = 0) {
        return (new self(0))
            ->setRed($r)
            ->setGreen($g)
            ->setBlue($b)
            ->setAlpha($a);
    }

    /**
     * @param string $value in RGB or ARGB hex-format, for example #ff00ff00
     * @return Color
     */
    public static function fromString($value) {
        if (!$value || substr($value, 0, 1) !== '#' ||  !in_array(strlen($value), [7, 9]))
            throw new \InvalidArgumentException('value');

        return new self(hexdec(substr($value, 1)));
    }

    public function __toString() {
        $minLength = $this->getAlpha() ? 8 : 6;
        $strval = dechex($this->value);
        $zeros = str_repeat('0', $minLength - strlen($strval));
        return "#$zeros$strval";
    }

    public function toArray() {
        return [$this->getRed(), $this->getGreen(), $this->getBlue(), $this->getAlpha()];
    }

    public function getRed() {
        return 0xFF & ($this->value >> 16);
    }

    public function setRed($value) {
        $this->value |= $value << 16;
        return $this;
    }

    public function getGreen() {
        return 0xFF & ($this->value >> 8);
    }

    public function setGreen($value) {
        $this->value |= $value << 8;
        return $this;
    }

    public function getBlue() {
        return 0xFF & $this->value;
    }

    public function setBlue($value) {
        $this->value |= $value;
        return $this;
    }

    public function getAlpha() {
        return $this->value >> 24;
    }

    public function setAlpha($value) {
        $this->value |= $value << 24;
        return $this;
    }
}
