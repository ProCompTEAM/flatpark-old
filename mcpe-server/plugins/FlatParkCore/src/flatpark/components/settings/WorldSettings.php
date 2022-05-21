<?php
namespace flatpark\components\settings;

use flatpark\Events;
use flatpark\defaults\EventList;
use flatpark\components\base\Component;
use pocketmine\event\block\BlockBurnEvent;

class WorldSettings extends Component
{
    public function initialize()
    {
        Events::registerEvent(EventList::BLOCK_BURN_EVENT, [$this, "applyBlockBurnSettings"]);
    }

    public function getAttributes() : array
    {
        return [
        ];
    }

    public function applyBlockBurnSettings(BlockBurnEvent $event)
    {
        $event->cancel();
    }
}
