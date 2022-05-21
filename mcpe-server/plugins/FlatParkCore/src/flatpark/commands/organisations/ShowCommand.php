<?php
namespace flatpark\commands\organisations;

use flatpark\Components;
use pocketmine\event\Event;
use flatpark\components\chat\Chat;

use flatpark\defaults\Permissions;
use flatpark\common\player\FlatParkPlayer;
use flatpark\defaults\OrganisationConstants;
use flatpark\commands\base\OrganisationsCommand;
use flatpark\components\organisations\Organisations;

class ShowCommand extends OrganisationsCommand
{
    public const CURRENT_COMMAND = "show";

    private Organisations $organisations;
    
    private Chat $chat;

    public function __construct()
    {
        $this->organisations = Components::getComponent(Organisations::class);

        $this->chat = Components::getComponent(Chat::class);
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
        $organisationId = $player->getSettings()->organisation;

        if ($organisationId === OrganisationConstants::NO_WORK) {
            $player->sendMessage("CommandShowNoWork");
            return;
        }

        $organisationName = $this->organisations->getName($organisationId, false);

        $this->chat->sendLocalMessage($player, "{CommandShowHandLic}" . $organisationName . "*§8)", "§d : ", 10);
    }
}