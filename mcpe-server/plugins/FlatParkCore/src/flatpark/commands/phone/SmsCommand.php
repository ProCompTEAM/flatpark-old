<?php
namespace flatpark\commands\phone;

use flatpark\defaults\Sounds;

use flatpark\common\player\FlatParkPlayer;
use flatpark\defaults\Permissions;

use pocketmine\event\Event;
use flatpark\commands\base\Command;
use flatpark\Components;
use flatpark\components\phone\Phone;
use flatpark\utils\ArraysUtility;

class SmsCommand extends Command
{
    public const CURRENT_COMMAND = "sms";

    private Phone $phone;

    public function __construct()
    {
        $this->phone = Components::getComponent(Phone::class);
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
        $player->sendSound(Sounds::ENABLE_PHONE);

        if(self::argumentsNo($args)) {
            $this->phone->sendDisplayMessages($player);
        } elseif(self::argumentsMin(2, $args) and is_numeric($args[0])) {
            $this->phone->sendSms($player, $args[0], ArraysUtility::getStringFromArray($args, 1));
        } else {
            $player->sendMessage("PhoneCheckNum");
        }
    }
}