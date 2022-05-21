<?php
namespace flatpark\models;

class RepeatingActionStates
{
    public mixed $target;

    public array $arguments;

    public int $interval;

    public int $penIndex = 0;
}