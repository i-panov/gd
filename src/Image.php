<?php

namespace GD;

use GD\Structures\Point;
use GD\Structures\Size;
use GD\Structures\Rect;
use InvalidArgumentException;

class Image {
    const FORMATS = ['png', 'bmp', 'jpeg', 'gif'];

    /** @var resource $handle */
    private $handle;

    private function __construct($handle = null) {
        if (!$handle)
            throw new InvalidArgumentException('handle');

        $this->handle = $handle;
    }

    /**
     * @param Size $size
     * @return Image
     */
    public static function create($size) {
        return new self(imagecreatetruecolor($size->width, $size->height));
    }

    /**
     * @param string $filename
     * @return Image
     */
    public static function load($filename) {
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
    public function save($filename, $quality = null, $filters = null) {
        if (!$filename)
            throw new InvalidArgumentException('filename');

        $ext = pathinfo($filename, PATHINFO_EXTENSION) ?: 'png';

        if ($ext === 'jpg')
            $ext = 'jpeg';

        if (!in_array($ext, static::FORMATS))
            throw new InvalidArgumentException('invalid extension of file');

        $params = [$this->handle];

        if ($filename) {
            array_push($params, $filename);

            if ($quality)
                array_push($params, $quality);

            if ($quality and $filters)
                array_push($params, $filters);
        }

        return call_user_func_array("image$ext", $params);
    }

    public function getHandle() {
        return $this->handle;
    }

    public function getSize() {
        return Size::of(imagesx($this->handle), imagesy($this->handle));
    }

    /**
     * @param Size $size
     * @param int $mode
     */
    public function setSize($size, $mode = IMG_BILINEAR_FIXED) {
        imagescale($this->handle, $size->width, $size->height, $mode);
    }

    public function isTrueColor() {
        return imageistruecolor($this->handle);
    }

    public function setSaveAlpha($value = false) {
        return imagesavealpha($this->handle, $value);
    }

    public function setAlphaBlendingEnabled($value = false) {
        return imagealphablending($this->handle, $value);
    }

    /**
     * @param Rect $rect
     * @return Image
     */
    public function crop($rect) {
        return new Image(imagecrop($this->handle, [
            'x' => $rect->location->x,
            'y' => $rect->location->y,
            'width' => $rect->size->width,
            'height' => $rect->size->height
        ]));
    }

    /**
     * @param string $text
     * @param string $fontFilename
     * @param float $fontSize
     * @param Color $color
     * @param Point $location
     * @param int $angle
     * @return bool
     */
    public function drawText($text, $fontFilename, $fontSize, $color, $location, $angle = 0) {
        return imagettftext($this->handle, $fontSize, $angle, $location->x, $location->y, $color->value, $fontFilename, $text) !== false;
    }

    /**
     * @param Rect $rect
     * @param Color $color
     * @param bool $fill
     * @return bool
     */
    public function drawRectangle($rect, $color, $fill = false) {
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
