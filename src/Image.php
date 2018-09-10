<?php

namespace GD;

use GD\Structures\Point;
use GD\Structures\Size;
use GD\Structures\Rect;
use InvalidArgumentException;

class Image {
    const ALIGN_START = -1, ALIGN_CENTER = 0, ALIGN_END = 1;

    /** @var resource $_handle */
    private $_handle;

    /** @var int */
    private $_lineThickness = 1;

    /** @var string */
    public $fontFilename;

    /** @var float */
    public $fontSize = 10.0;

    /** @var Color */
    public $fontColor;

    /** @var float */
    public $fontRotation = 0.0;

    /** @var float */
    public $textLineHeight = 1.5;

    /** @var float */
    public $textAlignOffset = 0.0;

    private function __construct($handle) {
        if (!$handle)
            throw new InvalidArgumentException('handle was null');

        $this->_handle = $handle;
        $this->fontColor = new Color();
    }

    public static function create(Size $size): Image {
        return new self(imagecreatetruecolor($size->width, $size->height));
    }

    public static function load(string $filename): Image {
        $content = file_get_contents($filename);

        if ($content === false)
            throw new InvalidArgumentException("Can't open $filename");

        return new self(imagecreatefromstring($content));
    }

    public function __destruct() {
        if ($this->_handle)
            imagedestroy($this->_handle);
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

        return call_user_func("image$ext", $this->_handle, $filename, $quality, $filters);
    }

    public function handle() {
        return $this->_handle;
    }

    public function getSize() {
        return Size::of(imagesx($this->_handle), imagesy($this->_handle));
    }

    public function setSize(Size $size, int $mode = IMG_BILINEAR_FIXED) {
        imagescale($this->_handle, $size->width, $size->height, $mode);
    }

    public function isTrueColor() {
        return imageistruecolor($this->_handle);
    }

    public function enableAlpha() {
        $this->disableAlphaBlending();
        return imagesavealpha($this->_handle, true);
    }

    public function disableAlpha() {
        return imagesavealpha($this->_handle, false);
    }

    public function enableAlphaBlending() {
        return imagealphablending($this->_handle, true);
    }

    public function disableAlphaBlending() {
        return imagealphablending($this->_handle, false);
    }

    public function crop(Rect $rect): Image {
        return new Image(imagecrop($this->_handle, [
            'x' => $rect->location->x,
            'y' => $rect->location->y,
            'width' => $rect->size->width,
            'height' => $rect->size->height
        ]));
    }

    public function getLineThickness() {
        return $this->_lineThickness;
    }

    public function setLineThickness(int $value) {
        if (imagesetthickness($this->_handle, $value)) {
            $this->_lineThickness = $value;
            return true;
        }

        return false;
    }

    public function drawRectangle(Rect $rect, Color $color, bool $fill = false): bool {
        $lt = $rect->leftTop();
        $rb = $rect->rightBottom();

        return call_user_func_array($fill ? 'imagefilledrectangle' : 'imagerectangle', [
            $this->_handle,
            $lt->x, $lt->y,
            $rb->x, $rb->y,
            $color->value
        ]);
    }

    public function drawText(string $text, Point $location): bool {
        return imagettftext(
                $this->_handle,
                $this->fontSize,
                $this->fontRotation,
                $location->x,
                $location->y,
                $this->fontColor->value,
                $this->fontFilename,
                $text
            ) !== false;
    }

    public function drawTextBox(string $text, Rect $rect, int $horizontalAlign = self::ALIGN_CENTER, int $verticalAlign = self::ALIGN_CENTER) {
        $sourceFontSize = $this->fontSize;
        $text = $this->wordwrap($text, $rect->size->width);

        do {
            $textBox = Font::computeTextBox($text, $this->fontFilename, $this->fontSize--, $this->fontRotation);
        } while ($textBox->size->height > $rect->size->height);

        $this->fontSize++;
        $rect->location->y += $this->fontSize + ($rect->size->height - $textBox->size->height) * $this->getAlignMultiplier($verticalAlign);

        foreach (explode("\n", $text) as $line) {
            $lineBox = Font::computeTextBox($line, $this->fontFilename, $this->fontSize, $this->fontRotation);
            $x = $rect->location->x + ($rect->size->width - $lineBox->size->width) * $this->getAlignMultiplier($horizontalAlign);

            if (!$this->drawText($line, Point::of($x, $rect->location->y)))
                return false;

            $rect->location->y += $this->fontSize * $this->textLineHeight;
        }

        $this->fontSize = $sourceFontSize;
        return true;
    }

    private function wordwrap($text, $width) {
        $result = '';

        foreach (explode(' ', html_entity_decode($text)) as $word) {
            $box = Font::computeTextBox("$result $word", $this->fontFilename, $this->fontSize, $this->fontRotation);

            if (!$result)
                $result = $word;
            else
                $result .= ($box->size->width > $width ? "\n" : " ") . $word;
        }

        return $result;
    }

    private function getAlignMultiplier($align) {
        $offset = $this->textAlignOffset;

        if ($offset < 0 || $offset >= 0.5)
            throw new InvalidArgumentException("invalid offset $offset");

        switch ($align) {
            case self::ALIGN_CENTER:
                return 0.5;
            case self::ALIGN_START:
                return $offset;
            case self::ALIGN_END:
                return 1 - $offset;
            default:
                throw new InvalidArgumentException("unknown align $align");
        }
    }
}
