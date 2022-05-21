<?php

namespace flatpark\commands\admin;

use flatpark\commands\base\Command;
use flatpark\common\player\FlatParkPlayer;
use flatpark\Components;
use flatpark\components\administrative\BanSystem;
use flatpark\defaults\Permissions;
use flatpark\Providers;
use flatpark\providers\data\UsersDataProvider;
use pocketmine\event\Event;

class UnbanCommand extends Command
{
    public const COMMAND_NAME = "unban";

    public const COMMAND_ALIAS = "pardon";

    private UsersDataProvider $usersDataProvider;

    private BanSystem $banSystem;

    public function __construct()
    {
        $this->usersDataProvider = Providers::getUsersDataProvider();

        $this->banSystem = Components::getComponent(BanSystem::class);
    }

    public function getCommand() : array
    {
        return [
            self::COMMAND_NAME,
            self::COMMAND_ALIAS
        ];
    }

    public function getPermissions() : array
    {
        return [
            Permissions::OPERATOR,
            Permissions::ADMINISTRATOR
        ];
    }

    public function execute(FlatParkPlayer $player, array $args = array(), Event $event = null)
    {
        if(self::argumentsNo($args)) {
            $this->sendInvalidCommandUsage($player);
            return;
        }

        $userName = $args[0];

        if(!$this->usersDataProvider->isUserExist($userName)) {
            $player->sendMessage("§eИгрока§b $userName §eне существует!");
            return;
        }

        $status = $this->banSystem->pardonUser($userName, $player->getName());

        if($status) {
            $player->sendMessage("§eИгрок§b $userName §eразблокирован!");
        } else {
            $player->sendMessage("§eИгрок§b $userName §eне является заблокированным!");
        }
    }

    private function sendInvalidCommandUsage(FlatParkPlayer $player)
    {
        $player->sendMessage("§eНеверное использование данной команды. Формат: §b/unban (имя игрока)");
    }
}