<?php
namespace flatpark\commands\organisations;

use flatpark\Providers;
use flatpark\Components;
use pocketmine\event\Event;

use flatpark\components\chat\Chat;
use flatpark\defaults\Permissions;
use flatpark\providers\MapProvider;
use flatpark\providers\BankingProvider;
use flatpark\common\player\FlatParkPlayer;
use flatpark\defaults\OrganisationConstants;
use flatpark\commands\base\OrganisationsCommand;
use flatpark\components\organisations\Organisations;

class HealCommand extends OrganisationsCommand
{
    public const CURRENT_COMMAND = "heal";

    public const POINT_NAME = "Больница";

    private MapProvider $mapProvider;

    private BankingProvider $bankingProvider;

    private Chat $chat;

    public function __construct()
    {
        $this->mapProvider = Providers::getMapProvider();

        $this->bankingProvider = Providers::getBankingProvider();

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
        if (!$this->isHealer($player)) {
            $player->sendMessage("CommandHealNoCanHeal");
            return;
        }

        if (!$this->isNearPoint($player)) {
            $player->sendMessage("CommandHealNoHospital");
            return;
        }

        $plrs = $this->getPlayersNear($player);

        if ($plrs == 1) {
            $this->healPlayer($player, $plrs[0]);
        } elseif ($plrs > 1) {
            $this->moveThemOut($plrs, $player);
        } else {
            $player->sendMessage("CommandHealNoPlayers");
        }
    }

    private function isHealer(FlatParkPlayer $plr)
    {
        return $plr->getSettings()->organisation === OrganisationConstants::DOCTOR_WORK;
    }

    private function isNearPoint(FlatParkPlayer $player) : bool
    {
        $plist = $this->mapProvider->getNearPoints($player->getPosition(), 32);

        return in_array(self::POINT_NAME, $plist);
    }

    private function moveThemOut(array $plrs, FlatParkPlayer $healer)
    {
        $this->chat->sendLocalMessage($healer, "{CommandHealManyPlayers1}");

        foreach($plrs as $id => $p) {
            if($id > 1) {
                $p->sendMessage("CommandHealManyPlayers2");
            }
        }

        $healer->sendMessage("CommandHealManyPlayers3");
    }

    private function getPlayersNear(FlatParkPlayer $player) : array
    {
        $allplayers = $this->getCore()->getRegionPlayers($player->getPosition(), 5);

        $players = array();

        foreach ($allplayers as $currp) {
            if ($currp->getName() != $player->getName()) {
                $players[] = $currp;
            }
        }

        return $players;
    }

    private function healPlayer(FlatParkPlayer $healer, FlatParkPlayer $playerToHeal)
    {
        $playerToHeal->getEffects()->clear();
        $playerToHeal->setHealth($playerToHeal->getMaxHealth());

        $this->g->sendLocalMessage($healer, "{CommandHealDo}");
        $this->bankingProvider->givePlayerMoney($healer, 500);
    }
}