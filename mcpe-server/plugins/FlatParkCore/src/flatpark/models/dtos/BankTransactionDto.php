<?php
namespace flatpark\models\dtos;

class BankTransactionDto extends BaseDto
{
    public string $name;

    public float $amount;
}