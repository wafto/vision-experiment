<?php

namespace App;

use GdImage;
use RuntimeException;

final class Image
{
    private ?GdImage $source;

    readonly public int $width;

    readonly public int $height;

    public function __construct(GdImage $source)
    {
        $this->source = $source;
        $this->width = \imagesx($this->source);
        $this->height = \imagesy($this->source);
    }

    public function __destruct()
    {
        \imagedestroy($this->source);
    }

    public static function makeFromJPG(string $filepath): self
    {
        $source = \imagecreatefromjpeg($filepath);

        if ($source === false) {
            throw new RuntimeException(sprintf('Unable to open JPEG file on path %s', $filepath));
        }

        return new self($source);
    }

    public function saveJPG(string $filepath): void
    {
        if (\imagejpeg($this->source, $filepath, 100) !== true) {
            throw new RuntimeException(sprintf('Unable to save JPEG file on path %s', $filepath));
        }
    }

    public function paintBlock(Block $block, int $r, int $g, int $b): self
    {
        $copy = \imagecreatetruecolor($this->width, $this->height);
        \imagecopy($copy, $this->source, 0, 0, 0, 0, $this->width, $this->height);
        $color = \imagecolorallocate($copy, $r, $g, $b);

        \imageline($copy, $block->p1->x, $block->p1->y, $block->p2->x, $block->p2->y, $color);
        \imageline($copy, $block->p2->x, $block->p2->y, $block->p3->x, $block->p3->y, $color);
        \imageline($copy, $block->p3->x, $block->p3->y, $block->p4->x, $block->p4->y, $color);
        \imageline($copy, $block->p4->x, $block->p4->y, $block->p1->x, $block->p1->y, $color);

        return new self($copy);
    }

    public function cropBlock(Block $block): self
    {
        $canvassize = \intval(\ceil(\sqrt($this->width * $this->width + $this->height * $this->height)));
        $canvas = \imagecreatetruecolor($canvassize + 1, $canvassize + 1);
        $canvascenter = Point::make(
            \intdiv($canvassize, 2) + 1,
            \intdiv($canvassize, 2) + 1,
        );

        /** Temporal rotated image with the block angle to have the block "fully" horizontal  */
        $rotated = \imagerotate($this->source, $block->angle, 0);
        $rotatedwidth = \imagesx($rotated);
        $rotatedheight = \imagesy($rotated);
        $rotatedcenter = Point::make(
            \intdiv($rotatedwidth, 2),
            \intdiv($rotatedheight, 2),
        );

        /** Create a canvas for managing with more easy the rotation and future cropping */
        imagecopy(
            $canvas,
            $rotated,
            $canvascenter->x - $rotatedcenter->x,
            $canvascenter->y - $rotatedcenter->y,
            0,
            0,
            $rotatedwidth,
            $rotatedheight,
        );

        /** Rotated image is not longer needed */
        \imagedestroy($rotated);

        $offsetx = \intdiv($canvassize - $this->width, 2);
        $offsety = \intdiv($canvassize - $this->height, 2);

        $normalizedblock = Block::make(
            Point::make($offsetx + $block->p1->x, $offsety + $block->p1->y),
            Point::make($offsetx + $block->p2->x, $offsety + $block->p2->y),
            Point::make($offsetx + $block->p3->x, $offsety + $block->p3->y),
            Point::make($offsetx + $block->p4->x, $offsety + $block->p4->y),
        )->rotate($canvascenter, 360 - $block->angle);

        $copy = \imagecreatetruecolor($normalizedblock->width, $normalizedblock->height);
        \imagecopy($copy, $canvas, 0, 0, $normalizedblock->p1->x, $normalizedblock->p1->y, $normalizedblock->width, $normalizedblock->height);

        \imagedestroy($canvas);

        return new self($copy);
    }
}
