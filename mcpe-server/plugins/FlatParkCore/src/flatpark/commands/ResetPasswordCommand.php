<?php
namespace flatpark\commands;

use pocketmine\event\Event;

use flatpark\defaults\Permissions;
use flatpark\commands\base\Command;
use flatpark\common\player\FlatParkPlayer;
use flatpark\Providers;
use flatpark\providers\data\UsersDataProvider;

class ResetPasswordCommand extends Command
{
    public const CURRENT_COMMAND = "resetpassword";

    private UsersDataProvider $usersDataProvider;

    public function __construct()
    {
        $this->usersDataProvider = Providers::getUsersDataProvider();
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
            Permissions::OPERATOR,
            Permissions::ADMINISTRATOR
        ];
    }

    public function execute(FlatParkPlayer $player, array $args = array(), Event $event = null)
    {
        if(self::argumentsNo($args)) {
            $player->sendMessage("CommandResetPasNoName");
            return;
        }
        
        $targetPlayerName = $args[0];

        if(!$this->usersDataProvider->isUserExist($targetPlayerName)){
            $player->sendMessage("CommandResetPasNameNoExist");
            return;
        }
        
        $this->resetPassword($player, $targetPlayerName);
    }

    private function resetPassword(FlatParkPlayer $sender, string $targetPlayerName)
    {
        $this->usersDataProvider->resetUserPassword($targetPlayerName);
        $sender->sendLocalizedMessage("{CommandResetPasSucces1} $targetPlayerName {CommandResetPasSucces2}");
        $targetPlayer = $this->getServer()->getPlayerByPrefix($targetPlayerName);

        if (isset($targetPlayer)) { 
            $targetPlayer->kick("CommandResetPasForPlayer");
        }
    }
}