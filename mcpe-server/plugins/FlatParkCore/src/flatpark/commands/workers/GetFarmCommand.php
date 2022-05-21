<?php
namespace flatpark\commands\workers;

use flatpark\defaults\Sounds;

use flatpark\common\player\FlatParkPlayer;
use flatpark\defaults\Permissions;

use pocketmine\event\Event;
use flatpark\commands\base\Command;
use flatpark\Components;
use flatpark\components\organisations\Organisations;

class GetFarmCommand extends Command
{
    public const CURRENT_COMMAND = "getf";

    private Organisations $organisations;

    public function __construct()
    {
        $this->organisations = Components::getComponent(Organisations::class);
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
        $this->organisations->getFarm()->getHarvest($player);

        $player->sendSound(Sounds::ROLEPLAY);
    }
}