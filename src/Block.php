<?php

namespace App;

readonly final class Block
{
    public readonly float $angle;

    public readonly int $width;

    public readonly int $height;

    public function __construct(
        public Point $p1,
        public Point $p2,
        public Point $p3,
        public Point $p4,
    ) {
        $angle = static function (Point $a, Point $b) {
            return \atan2($b->y - $a->y, $b->x - $a->x) * 180 / M_PI;
        };

        $distance = static function (Point $a, Point $b) {
            return \sqrt(\pow($b->x - $a->x, 2) + \pow($b->y - $a->y, 2));
        };

        $this->width = \intval(
            \round(\max($distance($this->p1, $this->p2), $distance($this->p4, $this->p3)))
        );

        $this->height = \intval(
            \round(\max($distance($this->p2, $this->p3), $distance($this->p1, $this->p4)))
        );

        $this->angle = $angle($this->p1, $this->p2);
    }

    public static function make(Point $p1, Point $p2, Point $p3, Point $p4): self
    {
        return new self($p1, $p2, $p3, $p4);
    }

    public function rotate(Point $center, float $angle): self
    {
        return new self(
            $this->p1->rotate($center, $angle),
            $this->p2->rotate($center, $angle),
            $this->p3->rotate($center, $angle),
            $this->p4->rotate($center, $angle),
        );
    }
}
