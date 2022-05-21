<?php
namespace flatpark\commands\permissions;

use flatpark\commands\base\Command;
use flatpark\common\player\FlatParkPlayer;
use flatpark\Components;
use flatpark\components\administrative\PermissionsSwitch;
use flatpark\defaults\Permissions;
use pocketmine\event\Event;

class SwitchCommand extends Command
{
    private const COMMAND_NAME = "q";

    private PermissionsSwitch $permissionsSwitch;

    public function __construct()
    {
        $this->permissionsSwitch = Components::getComponent(PermissionsSwitch::class);
    }

    public function getCommand(): array
    {
        return [
            self::COMMAND_NAME
        ];
    }

    public function getPermissions(): array
    {
        return [
            Permissions::ANYBODY
        ];
    }

    public function execute(FlatParkPlayer $player, array $args = array(), ?Event $event = null)
    {
        if(!$this->canSwitch($player)) {
            $player->sendMessage("§eВы не имеете доступа к данной команде");
            return;
        }

        $player->sendForm($this->permissionsSwitch->generateForm($player));
    }

    private function canSwitch(FlatParkPlayer $player)
    {
        return $player->isOperator() or $this->permissionsSwitch->isOperator($player->getName());
    }
}