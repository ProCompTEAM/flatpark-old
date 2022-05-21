<?php
namespace flatpark\commands\organisations;

use pocketmine\event\Event;
use flatpark\defaults\Permissions;
use flatpark\common\player\FlatParkPlayer;

use flatpark\defaults\OrganisationConstants;
use flatpark\commands\base\OrganisationsCommand;
use flatpark\components\organisations\Organisations;

class RadioCommand extends OrganisationsCommand
{
    public const CURRENT_COMMAND = "r";

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
        if (self::argumentsNo($args)) {
            return $player->sendMessage("§eПравильное использование этой команды: /o r [ТЕКСТ]");
        }

        $organisationId = $player->getSettings()->organisation;

        if ($organisationId === OrganisationConstants::NO_WORK) {
            $player->sendMessage("§6У вас нет рации!");
            return;
        }

        $implodedMessage = implode(self::ARGUMENTS_SEPERATOR, $args);

        $generatedRadioMessage = "§d[РАЦИЯ] §7" . $player->getProfile()->fullName . " §4> §7" . $implodedMessage;

        foreach($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
            $onlinePlayer = FlatParkPlayer::cast($onlinePlayer);
            if ($onlinePlayer->getSettings()->organisation === $organisationId) {
                $onlinePlayer->sendMessage($generatedRadioMessage);
            }
        }
    }
}