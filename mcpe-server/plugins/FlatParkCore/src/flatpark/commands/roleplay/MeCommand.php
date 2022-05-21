<?php
namespace flatpark\commands\roleplay;

use flatpark\Components;

use pocketmine\event\Event;
use flatpark\defaults\Sounds;

use flatpark\components\chat\Chat;
use flatpark\components\administrative\Tracking;
use flatpark\defaults\Permissions;
use flatpark\commands\base\Command;
use flatpark\common\player\FlatParkPlayer;

class MeCommand extends Command
{
    public const CURRENT_COMMAND = "me";

    public const DISTANCE = 10;

    private Tracking $tracking;

    private Chat $chat;

    public function __construct()
    {
        $this->tracking = Components::getComponent(Tracking::class);

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
        $event->cancel();

        if(self::argumentsNo($args)) {
            $player->sendMessage("CommandRolePlayMeUse");
            return;
        }

        $message = implode(self::ARGUMENTS_SEPERATOR, $args);
        
        $this->chat->sendLocalMessage($player, $message, "§d", self::DISTANCE);
        $player->sendSound(Sounds::ROLEPLAY);

        $this->tracking->actionRP($player, $message, self::DISTANCE, "[ME]");
    }
}