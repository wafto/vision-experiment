<?php

namespace App;

readonly final class Point
{
    public function __construct(
        public int $x,
        public int $y,
    ) { }

    public static function make(int $x, int $y): self
    {
        return new self($x, $y);
    }

    public function rotate(Point $center, float $angle): self
    {
        $cos = \cos(\deg2rad($angle));
        $sin = \sin(\deg2rad($angle));

        return Point::make(
            \intval($center->x + $cos * ($this->x - $center->x) - $sin * ($this->y - $center->y)),
            \intval($center->y + $sin * ($this->x - $center->x) + $cos * ($this->y - $center->y)),
        );
    }
}