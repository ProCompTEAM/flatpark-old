<?php
namespace flatpark\commands;

use pocketmine\event\Event;

use flatpark\defaults\Permissions;
use flatpark\commands\base\Command;
use flatpark\common\player\FlatParkPlayer;
use pocketmine\world\World;

class DayCommand extends Command
{
    public const CURRENT_COMMAND = "day";

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
        $player->getWorld()->setTime(World::TIME_DAY);
        $player->sendLocalizedMessage("{CommandDay}" . $player->getWorld()->getDisplayName());
    }
}