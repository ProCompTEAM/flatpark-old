<?php
namespace flatpark\models\player;

use pocketmine\world\Position;
use flatpark\common\player\FlatParkPlayer;
use flatpark\components\vehicles\models\base\BaseCar;

class StatesMap
{
    public bool $authorized;

    public bool $isNew;

    public bool $isBeginner;

    public ?Position $gps;

    public ?string $bar;

    public ?FlatParkPlayer $phoneCompanion;

    public ?FlatParkPlayer $phoneIncomingCall;

    public ?FlatParkPlayer $phoneOutcomingCall;

    public array $goods;

    public ?int $loadWeight;

    public bool $damageDisabled;

    public int $paymentMethod;

    public int $lastTap;

    public ?BaseCar $ridingVehicle;

    public ?BaseCar $rentedVehicle;

    public bool $gpsLightsVisible;

    public ?BossBarSession $bossBarSession;
}