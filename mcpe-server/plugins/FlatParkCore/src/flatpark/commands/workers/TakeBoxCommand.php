<?php
namespace flatpark\commands\workers;

use flatpark\Components;

use pocketmine\event\Event;
use flatpark\defaults\Sounds;

use flatpark\defaults\Permissions;
use flatpark\commands\base\Command;
use flatpark\common\player\FlatParkPlayer;
use flatpark\components\organisations\Organisations;

class TakeBoxCommand extends Command
{
    public const CURRENT_COMMAND = "takebox";

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
        $this->organisations->getWorkers()->takeBox($player);
        
        $player->sendSound(Sounds::ROLEPLAY);
    }
}