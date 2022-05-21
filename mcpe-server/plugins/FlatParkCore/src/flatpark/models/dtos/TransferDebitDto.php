<?php
namespace flatpark\models\dtos;

class TransferDebitDto extends BaseDto
{
    public string $name;

    public string $target;

    public float $amount;
}