<?php
namespace flatpark\models\dtos;

class PositionDto extends BaseDto
{
    public string $world;

    public float $x;

    public float $y;

    public float $z;
}