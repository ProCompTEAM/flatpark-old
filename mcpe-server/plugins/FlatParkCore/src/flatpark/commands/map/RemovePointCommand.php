<?php
namespace flatpark\commands\map;

use flatpark\Providers;

use pocketmine\event\Event;
use flatpark\defaults\Permissions;

use flatpark\commands\base\Command;
use flatpark\common\player\FlatParkPlayer;
use flatpark\providers\MapProvider;

class RemovePointCommand extends Command
{
    public const CURRENT_COMMAND = "rempoint";

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
        if(self::argumentsNo($args)) {
            $player->sendMessage("PointNoArg");
            return;
        }

        $status = $this->mapProvider->removePoint($args[0]);
        
        $player->sendMessage($status ? "CommandRemovePointSuccess" : "CommandRemovePointUnsuccess");
    }
}