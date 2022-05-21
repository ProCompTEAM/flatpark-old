<?php
namespace flatpark\models\dtos;

class LocalFloatingTextDto extends BaseDto
{
    public string $text;

    public string $world;

    public float $x;

    public float $y;

    public float $z;
}