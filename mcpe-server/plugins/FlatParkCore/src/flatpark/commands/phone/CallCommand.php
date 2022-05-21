<?php
namespace flatpark\commands\phone;

use flatpark\defaults\Sounds;

use flatpark\common\player\FlatParkPlayer;
use flatpark\defaults\Permissions;

use pocketmine\event\Event;
use flatpark\commands\base\Command;
use flatpark\Components;
use flatpark\components\chat\Chat;
use flatpark\components\phone\Phone;

class CallCommand extends Command
{
    public const CURRENT_COMMAND = "c";

    private Phone $phone;

    private Chat $chat;

    public function __construct()
    {
        $this->phone = Components::getComponent(Phone::class);

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
        $this->chat->sendLocalMessage($player, "{CommandCallPhoneTake}", "§d : ", 10);

        $player->sendSound(Sounds::ENABLE_PHONE, null, 20);

        if(self::argumentsNo($args)) {
            $this->phone->sendDisplayMessages($player);
            return;
        }

        if (!isset($args[0])) {
            $player->sendMessage("PhoneCheckNum");
        } elseif(is_numeric($args[0])) {
            $this->phone->initializeCallRequest($player, $args[0]);
        } else {
            $this->phone->acceptOrEndCall($player, $args[0]);
        }
    }
}