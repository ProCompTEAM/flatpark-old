<?php
namespace flatpark\commands;

use pocketmine\event\Event;
use flatpark\defaults\Permissions;

use flatpark\commands\base\Command;
use flatpark\common\player\FlatParkPlayer;

class LevelCommand extends Command
{
    public const CURRENT_COMMAND = "lvl";

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
            $player->sendMessage("CommandLevelUse");
            return;
        }

        $lvl = $this->getServer()->getWorldManager()->getWorldByName($args[0]);
        if($lvl != null) {
            $player->teleport($lvl->getSafeSpawn());
        } else {
            $player->sendMessage("CommandLevelInvalid");
        }
    }
}