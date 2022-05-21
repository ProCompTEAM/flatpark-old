<?php
namespace flatpark\commands\organisations;

use flatpark\commands\base\OrganisationsCommand;
use pocketmine\event\Event;

use flatpark\defaults\Permissions;
use flatpark\common\player\FlatParkPlayer;
use flatpark\Providers;
use flatpark\providers\ProfileProvider;

class RemoveCommand extends OrganisationsCommand
{
    public const CURRENT_COMMAND = "remove";
    public const CURRENT_COMMAND_ALIAS = "reject";

    private ProfileProvider $profileProvider;

    public function __construct()
    {
        $this->playerSettings = Providers::getProfileProvider();
    }

    public function getCommand() : array
    {
        return [
            self::CURRENT_COMMAND,
            self::CURRENT_COMMAND_ALIAS
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
        if (!$this->isBoss($player)) {
            $player->sendMessage("CommandRemoveNoBoss");
            return;
        }

        $players = $this->getPlayersNear($player);

        if (isset($players[0])) {
            $this->tryRejectGuy($players[0], $player);
        } else if (isset($players[1])) {
            $player->sendMessage("CommandRemoveManyPlayers");
        } else {
            $player->sendMessage("CommandRemoveNoPlayers");
        }
    }

    private function tryRejectGuy(FlatParkPlayer $player, FlatParkPlayer $boss)
    {
        if ($player->getSettings()->organisation !== $boss->getSettings()->organisation) {
            $boss->sendMessage("CommandRemoveNoOrg");
            return;
        }

        $player->getSettings()->organisation = 0;
        $this->profileProvider->saveSettings($player);

        $boss->sendLocalizedMessage("{CommandRemoveDo1}" . $player->getProfile()->fullName);
        $player->sendLocalizedMessage("{CommandRemoveDo2}". $boss->getProfile()->fullName ."!");
    }

    private function getPlayersNear(FlatParkPlayer $player) : array
    {
        $allplayers = $this->getCore()->getRegionPlayers($player->getPosition(), 5);

        $players = array();
        foreach ($allplayers as $currp) {
            if ($currp->getName() !== $player->getName()) {
                $players[] = $currp;
            }
        }

        return $players;
    }
}