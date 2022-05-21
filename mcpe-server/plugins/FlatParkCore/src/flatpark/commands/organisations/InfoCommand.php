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

class InfoCommand extends OrganisationsCommand
{
    public const CURRENT_COMMAND = "info";

    private Chat $chat;

    public function __construct()
    {
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
        if (!$this->canGetInfo($player)) {
            $player->sendMessage("CommandInfoNoCan");
            return;
        }

        $this->chat->sendLocalMessage($player, "{CommandInfoPrint}", "§d : ", 10);

        $plrs = $this->getPlayersNear($player);

        if (self::argumentsNo($plrs)) {
            $player->sendMessage("CommandInfoNoPlayer");
            return;
        }

        if (self::argumentsMin(2, $plrs)) {
            $player->sendMessage("CommandInfoManyPlayer");
        }

        $this->getPlayerInfo($plrs[0], $player);
    }

    private function canGetInfo(FlatParkPlayer $p) : bool
    {
        return $p->getSettings()->organisation == OrganisationConstants::GOVERNMENT_WORK or $p->getSettings()->organisation == OrganisationConstants::SECURITY_WORK;
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

    private function getPlayerInfo(FlatParkPlayer $playerToInfo, FlatParkPlayer $requestor)
    {
        $f = "§bДоп.информация о человеке " . $playerToInfo->getProfile()->fullName . ":";

        $f .= "\n§a > §eДокументы: ". ($this->core->getApi()->existsAttr($playerToInfo, Api::ATTRIBUTE_HAS_PASSPORT) ? "§aда" : "§cнет");
        $f .= "\n§a > §eАрестован: ". ($this->core->getApi()->existsAttr($playerToInfo, Api::ATTRIBUTE_ARRESTED) ? "§aда" : "§cнет");
        $f .= "\n§a > §eВ розыске: ". ($this->core->getApi()->existsAttr($playerToInfo, Api::ATTRIBUTE_WANTED) ? "§aда" : "§cнет");

        $requestor->sendMessage($f);
    }
}