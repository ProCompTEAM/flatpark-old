<?php
namespace flatpark\commands;

use flatpark\Tasks;
use flatpark\Components;
use pocketmine\event\Event;
use flatpark\defaults\Sounds;
use flatpark\defaults\Permissions;
use flatpark\commands\base\Command;
use flatpark\defaults\TimeConstants;
use flatpark\defaults\VehicleConstants;
use flatpark\common\player\FlatParkPlayer;
use flatpark\components\vehicles\Vehicles;

class TransportCommand extends Command
{
    public const CURRENT_COMMAND = "t";

    private Vehicles $vehicles;

    public function __construct()
    {
        $this->vehicles = Components::getComponent(Vehicles::class);
    }

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
        if(self::argumentsNo($args)) {
            $player->sendMessage("CommandTransportErrorSpawn");

            return;
        }

        $subCommand = $args[0];
        
        if($subCommand == "spawn") {
            if(!$player->isAdministrator()) {
                $player->sendMessage("CommandTransporNoPermission");

                return;
            }

            if(!self::argumentsMin(2, $args)) {
                $player->sendMessage("CommandTransportErrorSpawn");

                return;
            }

            if(!$this->spawnCar($player, $args[1])) {
                $player->sendMessage("CommandTransportErrorSpawnModel");

                return;
            }

            $player->sendMessage("Машина успешно создана.");
        } elseif($subCommand == "station") {
            //TODO: Add check: is @driver in @vehicle only

            if(!self::argumentsMin(2, $args)) {
                $player->sendMessage("CommandTransportErrorStation");

                return;
            }

            $this->broadcastTrainStation($player, Sounds::TRAIN_STATION, 0);
            $this->broadcastTrainStation($player, $this->getSoundForStationNumber($args[1]), 3);
        }
    }

    public function broadcastTrainStationSound(FlatParkPlayer $driver, string $sound)
    {
        foreach($this->getCore()->getRegionPlayers($driver->getPosition(), VehicleConstants::PLAYER_NEAR_STATION_DISTANCE) as $player) {
            $player = FlatParkPlayer::cast($player);
            $player->sendSound($sound);
        }
    }

    private function broadcastTrainStation(FlatParkPlayer $driver, string $sound, int $delaySeconds)
    {
        Tasks::registerDelayedAction(
            $delaySeconds * TimeConstants::ONE_SECOND_TICKS, 
            [$this, "broadcastTrainStationSound"],
            [$driver, $sound]
        );
    }

    private function getSoundForStationNumber(string $stationCode) : string
    {
        return Sounds::TRAIN_STATION . $stationCode;
    }

    private function spawnCar(FlatParkPlayer $player, string $model) : bool
    {
        return $this->vehicles->createVehicle($model, $player->getWorld(), $player->getLocation(), $player->getLocation()->getYaw());
    }
}