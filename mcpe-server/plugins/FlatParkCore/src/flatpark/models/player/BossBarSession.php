<?php
namespace flatpark\models\player;

class BossBarSession
{
    public ?string $title;

    public ?int $percents;

    public int $fakeEntityId;

    public bool $loaded;
}