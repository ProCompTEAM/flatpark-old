<?php
namespace flatpark\commands\base;

use flatpark\Core;

use pocketmine\Server;
use pocketmine\event\Event;
use flatpark\common\player\FlatParkPlayer;

abstract class Command
{
    static public function argumentsNo(array $args) : bool
    {
        return !isset($args[0]);
    }

    static public function argumentsCount(int $count, array $args) : bool
    {
        return count($args) == $count;
    }

    static public function argumentsMin(int $count, array $args) : bool
    {
        return count($args) >= $count;
    }

    static public function argumentsInterval(int $minCount, int $maxCount, array $args) : bool
    {
        return count($args) >= $minCount and count($args) <= $maxCount;
    }

    public const ARGUMENTS_SEPERATOR = " ";

    abstract public function getCommand() : array;

    abstract public function getPermissions() : array;

    abstract public function execute(FlatParkPlayer $player, array $args = array(), Event $event = null);

    protected function getCore()
    {
        return Core::getActive();
    }

    protected function getServer()
    {
        return Server::getInstance();
    }
}