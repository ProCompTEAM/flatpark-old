<?php
namespace flatpark\commands\map;

use flatpark\Providers;

use pocketmine\event\Event;
use flatpark\defaults\Permissions;

use flatpark\commands\base\Command;
use flatpark\providers\MapProvider;
use flatpark\common\player\FlatParkPlayer;

class ToNearPointCommand extends Command
{
    public const CURRENT_COMMAND = "tonearpoint";

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
            Permissions::OPERATOR,
            Permissions::ADMINISTRATOR
        ];
    }

    public function execute(FlatParkPlayer $player, array $args = array(), Event $event = null)
    {
        $nearPoints = $this->mapProvider->getNearPoints($player->getPosition(), 15);

        if (isset($nearPoints[0]))  {
            $this->mapProvider->teleportPoint($player, $nearPoints[0]);
        } else {
            $player->sendMessage("CommandNoToNearPoint");
        }
    }
}