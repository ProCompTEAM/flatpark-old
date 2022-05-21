<?php
namespace flatpark\components\map;

use flatpark\common\player\FlatParkPlayer;
use flatpark\Tasks;
use flatpark\components\base\Component;
use flatpark\defaults\TimeConstants;
use pocketmine\entity\object\ItemEntity;

class ClearLagg extends Component
{
    public function initialize()
    {
        Tasks::registerRepeatingAction(TimeConstants::CLEAR_LAGG_INTERVAL, [$this, "clearItems"]);
    }

    public function getAttributes() : array
    {
        return [
        ];
    }

    public function clearItems()
    {
        foreach ($this->getServer()->getWorldManager()->getWorlds() as $world) {
            foreach ($world->getEntities() as $entity) {

                if ($entity instanceof ItemEntity) {
                    $entity->close();
                }

                if ($entity instanceof FlatParkPlayer) {
                    $entity->sendTip("ClearLagg");
                }

            }
        }
    }
}