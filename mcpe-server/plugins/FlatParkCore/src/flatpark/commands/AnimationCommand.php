<?php
namespace flatpark\commands;

use pocketmine\event\Event;
use flatpark\defaults\Permissions;

use flatpark\commands\base\Command;
use flatpark\common\player\FlatParkPlayer;

class AnimationCommand extends Command
{
    public const CURRENT_COMMAND = "anim";

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
        $player->sleepOn($player->getPosition()->subtract(0, 1, 0));
    }
}