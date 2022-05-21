<?php
namespace flatpark\providers\base;

use flatpark\Core;
use pocketmine\Server;

abstract class Provider
{
    protected function getCore()
    {
        return Core::getActive();
    }

    protected function getServer()
    {
        return Server::getInstance();
    }
}