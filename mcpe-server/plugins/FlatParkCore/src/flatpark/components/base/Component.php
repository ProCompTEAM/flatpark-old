<?php
namespace flatpark\components\base;

use flatpark\Core;
use pocketmine\Server;

abstract class Component
{
    abstract public function initialize();

    abstract public function getAttributes() : array;

    public function hasAttribute(string $attribute) : bool
    {
        return in_array($attribute, $this->getAttributes());
    }

    protected function getCore()
    {
        return Core::getActive();
    }

    protected function getServer()
    {
        return Server::getInstance();
    }
}