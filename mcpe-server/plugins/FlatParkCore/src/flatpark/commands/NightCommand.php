<?php
namespace flatpark\commands;

use pocketmine\event\Event;

use flatpark\defaults\Permissions;
use flatpark\commands\base\Command;
use flatpark\common\player\FlatParkPlayer;
use pocketmine\world\World;

class NightCommand extends Command
{
    public const CURRENT_COMMAND = "night";

    public function getCommand() : array
    {
        return [
            self::CURRENT_COMMAND
        ];
    }

    public function getPermissions() : array
    {
        return [
            Permissions::ADMINISTRATOR,
            Permissions::OPERATOR
        ];
    }

    public function execute(FlatParkPlayer $player, array $args = array(), Event $event = null)
    {
        $player->getWorld()->setTime(World::TIME_NIGHT);
        $player->sendLocalizedMessage("{CommandNight}" . $player->getWorld()->getDisplayName());
    }
}