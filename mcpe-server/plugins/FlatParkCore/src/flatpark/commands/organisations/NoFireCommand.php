<?php
namespace flatpark\commands\organisations;

use flatpark\Components;
use pocketmine\event\Event;
use flatpark\defaults\Permissions;

use flatpark\common\player\FlatParkPlayer;
use flatpark\defaults\OrganisationConstants;
use flatpark\commands\base\OrganisationsCommand;
use flatpark\components\organisations\Organisations;

class NoFireCommand extends OrganisationsCommand
{
    public const CURRENT_COMMAND = "nofire";

    public const CURRENT_COMMAND_ALIAS1 = "clean";
    public const CURRENT_COMMAND_ALIAS2 = "clear";

    private Organisations $organisations;

    public function __construct()
    {
        $this->organisations = Components::getComponent(Organisations::class);
    }

    public function getCommand() : array
    {
        return [
            self::CURRENT_COMMAND,
            self::CURRENT_COMMAND_ALIAS1,
            self::CURRENT_COMMAND_ALIAS2
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
        if($player->getSettings()->organisation !== OrganisationConstants::EMERGENCY_WORK) {
            $player->sendMessage("§cВы не являетесь работником службы спасения!");
            return;
        }

        $this->organisations->getNoFire()->putOutFire($player);
    }
}