<?php

namespace flatpark\models\dtos;

class UserBanRecordDto extends BaseDto
{
    public string $userName;

    public string $issuerName;

    public string $releaseDate;

    public string $reason;
}