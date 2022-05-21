<?php
namespace flatpark\components\vehicles;

use flatpark\Events;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\world\World;
use pocketmine\entity\Location;
use flatpark\defaults\EventList;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\entity\EntityFactory;
use flatpark\components\base\Component;
use pocketmine\entity\EntityDataHelper;
use flatpark\common\player\FlatParkPlayer;
use flatpark\defaults\ComponentAttributes;
use pocketmine\event\player\PlayerQuitEvent;
use flatpark\components\vehicles\models\TaxiCar;
use flatpark\components\vehicles\models\GuestCar1;
use flatpark\components\vehicles\models\GuestCar2;
use flatpark\components\vehicles\models\GuestCar3;
use flatpark\components\vehicles\models\GuestCar4;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InteractPacket;
use flatpark\components\vehicles\models\base\BaseCar;
use pocketmine\network\mcpe\protocol\PlayerInputPacket;

class Vehicles extends Component
{
    private array $vehicles;

    public function initialize()
    {
        Events::registerEvent(EventList::DATA_PACKET_RECEIVE_EVENT, [$this, "handleDataPacketReceive"]);
        Events::registerEvent(EventList::PLAYER_QUIT_EVENT, [$this, "processPlayerQuitEvent"]);

        $this->loadVehicles();
    }

    public function getAttributes(): array
    {
        return [
            ComponentAttributes::SHARED
        ];
    }

    public function processPlayerQuitEvent(PlayerQuitEvent $event)
    {
        $player = FlatParkPlayer::cast($event->getPlayer());

        if (isset($player->getStatesMap()->ridingVehicle)) {
            $player->getStatesMap()->ridingVehicle->tryToRemovePlayer($player);
        }

        if (isset($player->getStatesMap()->rentedVehicle)) {
            $player->getStatesMap()->rentedVehicle->removeRentedStatus();
        }
    }

    public function createVehicle(string $vehicleName, World $world, Location $location, float $yaw) : bool
    {
        $vehicleClassName = $this->getVehicle($vehicleName);

        if (!isset($vehicleClassName)) {
            return false;
        }

        $vehicle = new $vehicleClassName($location);
        $vehicle->saveNBT();

        $vehicle->spawnToAll();

        return true;
    }

    public function getVehicle(string $vehicleName) : ?string
    {
        if (!isset($this->getVehicles()[$vehicleName])) {
            return null;
        }

        return $this->getVehicles()[$vehicleName];
    }

    public function getVehicles()
    {
        return $this->vehicles;
    }

    public function handleDataPacketReceive(DataPacketReceiveEvent $event)
    {
        $packet = $event->getPacket();

        if($packet instanceof PlayerAuthInputPacket) {
            if($packet->getMoveVecX() === 0.0 and $packet->getMoveVecZ() === 0.0) {
                return;
            }

            $this->handleVehicleMove($event);
        } else if($packet instanceof InteractPacket) {
            if($packet->action !== InteractPacket::ACTION_LEAVE_VEHICLE) {
                return;
            }

            $vehicle = $event->getOrigin()->getPlayer()->getWorld()->getEntity($packet->targetActorRuntimeId);

            if($vehicle instanceof BaseCar) {
                $vehicle->tryToRemovePlayer($event->getOrigin()->getPlayer());
                $event->cancel();
            }
        }
    }

    protected function handleVehicleMove(DataPacketReceiveEvent $event)
    {
        $player = $event->getOrigin()->getPlayer();

        if($player->getStatesMap()->ridingVehicle === null) {
            return;
        }

        if($player->getStatesMap()->ridingVehicle?->getDriver()?->getName() !== $player->getName()) {
            return;
        }

        $player->getStatesMap()->ridingVehicle->updateSpeed($event->getPacket()->getMoveVecX(), $event->getPacket()->getMoveVecZ());
    }

    private function loadVehicles()
    {
        $this->vehicles = [
            "car1" => GuestCar1::class,
            "car2" => GuestCar2::class,
            "car3" => GuestCar3::class,
            "car4" => GuestCar4::class,
            "taxi" => TaxiCar::class
        ];

        foreach($this->getVehicles() as $name => $class) {
            EntityFactory::getInstance()->register($class, function(World $world, CompoundTag $nbt) use($class) : BaseCar {
                return new $class(EntityDataHelper::parseLocation($nbt, $world), $nbt);
            }, [$name]);
        }
    }
}