<?php
namespace flatpark\commands\economy;

use flatpark\Providers;
use pocketmine\event\Event;

use flatpark\defaults\Sounds;
use flatpark\defaults\Permissions;
use flatpark\commands\base\Command;
use flatpark\common\player\FlatParkPlayer;
use flatpark\Components;
use flatpark\components\chat\Chat;
use flatpark\providers\BankingProvider;

class PayCommand extends Command
{
    public const CURRENT_COMMAND = "pay";

    public const DISTANCE = 6;

    private BankingProvider $bankingProvider;

    private Chat $chat;

    public function __construct()
    {
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
        $player->sendSound(Sounds::CHAT_SOUND);

        if(self::argumentsNo($args) or !is_numeric($args[0])) {
            $player->sendMessage("CommandPayUse");
            return;
        }

        $players = $this->getCore()->getRegionPlayers($player->getPosition(), self::DISTANCE);

        if(count($players) > 2) {
            $player->sendMessage("CommandPayCountPlayer");
            return;
        }

        $this->chat->sendLocalMessage($player, "{CommandPayTake}", "§d : ", self::DISTANCE);
        foreach($players as $p) {
            if($p === $player) {
                continue;
            } else {
                if($this->bankingProvider->reduceCash($player, $args[0])) {
                    $this->chat->sendLocalMessage($player, "{CommandPayPay}", "§d", self::DISTANCE);
                    $this->bankingProvider->giveCash($p, $args[0]);
                } else {
                    $player->sendMessage("CommandPayNoMoney");
                }
            }
        }
        
        $this->chat->sendLocalMessage($player, "{CommandPayPut}", "§d", self::DISTANCE);
        
        $event->cancel();
    }
}