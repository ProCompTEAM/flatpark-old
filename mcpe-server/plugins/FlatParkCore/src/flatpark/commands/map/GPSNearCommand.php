<?php
namespace flatpark\commands\map;

use flatpark\Providers;

use pocketmine\event\Event;
use flatpark\defaults\Sounds;

use flatpark\defaults\Permissions;
use flatpark\commands\base\Command;
use flatpark\common\player\FlatParkPlayer;
use flatpark\providers\MapProvider;

class GPSNearCommand extends Command
{
    public const CURRENT_COMMAND = "gpsnear";

    public const DISTANCE = 40;

    private MapProvider $mapProvider;

    public function __construct()
    {
        $this->mapProvider = Providers::getMapProvider();
    }

    public function getCommand() : array
    {
        return [
            self::CURRENT_COMMAND
        ];
    }

    public function getPermissions() : array
    {
        return [
            Permissions::ANYBODY
        ];
    }

    public function execute(FlatParkPlayer $player, array $args = array(), Event $event = null)
    {
        $nearPoints = $this->mapProvider->getNearPoints($player->getPosition(), self::DISTANCE);
        $list = " §7(отсутствуют)  ";
        
        if (count($nearPoints) > 0) {
            $list = implode(", ", $nearPoints);
        }
        
        $player->sendLocalizedMessage("{CommandGPSNear}" . $list);
        $player->sendSound(Sounds::OPEN_NAVIGATOR);
    }
}