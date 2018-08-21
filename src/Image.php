<?php

namespace GD;

use GD\Structures\Point;
use GD\Structures\Size;
use GD\Structures\Rect;
use InvalidArgumentException;

class Image {
    /** @var resource $handle */
    private $handle;

    /** @var string */
    public $fontFilename;

    /** @var float */
    public $fontSize = 10;

    /** @var Color */
    public $fontColor;

    /** @var float */
    public $fontRotation = 0;

    private function __construct($handle) {
        if (!$handle)
            throw new InvalidArgumentException('handle was null');

        $this->handle = $handle;
        $this->fontColor = new Color();
    }

    public static function create(Size $size): Image {
        return new self(imagecreatetruecolor($size->width, $size->height));
    }

    public static function load(string $filename): Image {
        return new self(imagecreatefromstring(file_get_contents($filename)));
    }

    public function __destruct() {
        if ($this->handle)
            imagedestroy($this->handle);
    }

    /**
     * @param string $filename
     * @param int|null $quality
     * @param int|null $filters
     * @return bool
     */
    public function save(string $filename, $quality = null, $filters = null): bool {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION)) ?: 'png';

        if ($ext === 'jpg')
            $ext = 'jpeg';

        if (!in_array($ext, ['png', 'bmp', 'jpeg', 'gif']))
            throw new InvalidArgumentException('invalid extension of file');

        return call_user_func("image$ext", $this->handle, $filename, $quality, $filters);
    }

    public function handle() {
        return $this->handle;
    }

    public function getSize() {
        return Size::of(imagesx($this->handle), imagesy($this->handle));
    }

    public function setSize(Size $size, int $mode = IMG_BILINEAR_FIXED) {
        imagescale($this->handle, $size->width, $size->height, $mode);
    }

    public function isTrueColor() {
        return imageistruecolor($this->handle);
    }

    public function enableAlpha() {
        $this->disableAlphaBlending();
        return imagesavealpha($this->handle, true);
    }

    public function disableAlpha() {
        return imagesavealpha($this->handle, false);
    }

    public function enableAlphaBlending() {
        return imagealphablending($this->handle, true);
    }

    public function disableAlphaBlending() {
        return imagealphablending($this->handle, false);
    }

    public function crop(Rect $rect): Image {
        return new Image(imagecrop($this->handle, [
            'x' => $rect->location->x,
            'y' => $rect->location->y,
            'width' => $rect->size->width,
            'height' => $rect->size->height
        ]));
    }

    public function drawText(string $text, Point $location): bool {
        return imagettftext(
                $this->handle,
                $this->fontSize,
                $this->fontRotation,
                $location->x,
                $location->y,
                $this->fontColor->value,
                $this->fontFilename,
                $text
            ) !== false;
    }
    }

    public function drawRectangle(Rect $rect, Color $color, bool $fill = false): bool {
        $lt = $rect->leftTop();
        $rb = $rect->rightBottom();

        return call_user_func_array($fill ? 'imagefilledrectangle' : 'imagerectangle', [
            $this->handle,
            $lt->x, $lt->y,
            $rb->x, $rb->y,
            $color->value
        ]);
    }
}
