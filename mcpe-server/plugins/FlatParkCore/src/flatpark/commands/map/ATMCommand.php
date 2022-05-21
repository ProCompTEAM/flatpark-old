<?php
namespace flatpark\commands\map;

use flatpark\commands\base\Command;
use flatpark\common\player\FlatParkPlayer;
use flatpark\Components;
use flatpark\components\map\ATM;
use flatpark\defaults\MapConstants;
use flatpark\defaults\Permissions;
use flatpark\Providers;
use flatpark\providers\MapProvider;
use pocketmine\event\Event;

class ATMCommand extends Command
{
    private const COMMAND_NAME = "atm";

    private const ATM_DISTANCE = 4;

    private MapProvider $mapProvider;

    private ATM $atm;

    public function __construct()
    {
        $this->mapProvider = Providers::getMapProvider();

        $this->atm = Components::getComponent(ATM::class);
    }

    public function getCommand() : array
    {
        return [
            self::COMMAND_NAME
        ];
    }

    public function getPermissions() : array
    {
        return [
            Permissions::ANYBODY
        ];
    }

    public function execute(FlatParkPlayer $player, array $args = array(), ?Event $event = null)
    {
        if(!$this->isNearATM($player)) {
            $player->sendMessage("CommandATMNoNear");
            return;
        }

        $this->atm->sendMenu($player);
    }

    private function isNearATM(FlatParkPlayer $player) : bool
    {
        $points = $this->mapProvider->getNearPoints($player->getPosition(), self::ATM_DISTANCE, false);

        foreach($points as $point) {
            if($point->groupId === MapConstants::POINT_GROUP_ATM) {
                return true;
            }
        }

        return false;
    }
}