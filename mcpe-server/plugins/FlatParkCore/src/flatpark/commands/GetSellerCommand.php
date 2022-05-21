<?php
namespace flatpark\commands;

use flatpark\Providers;
use pocketmine\event\Event;
use flatpark\defaults\Sounds;
use pocketmine\world\Position;

use flatpark\defaults\Permissions;
use flatpark\commands\base\Command;
use flatpark\providers\MapProvider;
use flatpark\common\player\FlatParkPlayer;
use flatpark\defaults\OrganisationConstants;
use flatpark\components\organisations\Organisations;

class GetSellerCommand extends Command
{
    public const CURRENT_COMMAND = "getseller";

    public const DISTANCE = 10;

    private MapProvider $mapProvider;

    public function __construct()
    {
        $this->mapProvider = Providers::getMapProvider();
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
        $player->sendSound(Sounds::ROLEPLAY);

        $shopPoint = $this->getShop($player->getPosition());

        if($shopPoint == null) {
            $player->sendMessage("CommandGetSellerNoPoint");
            return;
        }

        foreach($this->getServer()->getOnlinePlayers() as $targetPlayer){
            $targetPlayer = FlatParkPlayer::cast($targetPlayer);
            if($targetPlayer->getSettings()->organisation == OrganisationConstants::SELLER_WORK) {
                $targetPlayer->sendMessage("CommandGetSellerCall1");
            }
        }

        $player->sendMessage("CommandGetSellerCall2");
        $player->sendMessage("CommandGetSellerCall3");
    }

    private function getShop(Position $position) : ?string
    {
        $shops = $this->mapProvider->getNearPoints($position, self::DISTANCE);
        
        foreach($shops as $point) {
            if($this->mapProvider->getPointGroup($point) == 2) {
                return $point;
            }
        }
        
        return null;
    }
}