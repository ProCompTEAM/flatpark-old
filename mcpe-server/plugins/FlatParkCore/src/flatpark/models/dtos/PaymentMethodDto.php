<?php
namespace flatpark\models\dtos;

class PaymentMethodDto extends BaseDto
{
    public string $name;

    public int $method;
}