<?php
namespace flatpark\models\dtos;

abstract class BaseDto
{
    public function set(array $data) {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }
}