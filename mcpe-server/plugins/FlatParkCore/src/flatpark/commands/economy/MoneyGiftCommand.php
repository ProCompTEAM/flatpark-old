<?php
namespace flatpark\commands\economy;

use flatpark\Providers;
use pocketmine\event\Event;

use flatpark\defaults\Sounds;
use flatpark\defaults\Permissions;
use flatpark\commands\base\Command;
use flatpark\common\player\FlatParkPlayer;
use flatpark\providers\BankingProvider;

class MoneyGiftCommand extends Command
{
    public const CURRENT_COMMAND = "moneygift";

    private const MAX_GIFT_SUM = 1000000;

    private BankingProvider $bankingProvider;

    public function __construct()
    {
        $this->bankingProvider = Providers::getBankingProvider();
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
            Permissions::OPERATOR
        ];
    }

    public function execute(FlatParkPlayer $player, array $args = array(), Event $event = null)
    {
        if(!self::argumentsMin(2, $args) or !is_numeric($args[1])) {
            $player->sendMessage("CommandMoneyGiftUsage");
            return;
        }

        $targetPlayerName = $args[0];
        $sum = $args[1];

        $player->sendSound(Sounds::ROLEPLAY);

        if($sum > self::MAX_GIFT_SUM) {
            $player->sendMessage("CommandMoneyGiftMax");
            return;
        }

        $targetPlayer = $this->getServer()->getPlayerByPrefix($targetPlayerName);
        if(!is_null($targetPlayer)) {
            $this->transferMoney($player, $targetPlayer, $sum);
            $this->notifyTargetPlayer($targetPlayer, $sum);
            $this->notifyOperators($player, $targetPlayer, $sum);
        } else {
            $player->sendMessage("CommandMoneyGiftNoPlayer");
        }
    }

    private function transferMoney(FlatParkPlayer $operator, FlatParkPlayer $targetPlayer, float $sum)
    {
        //transfer sum through operator for datacenter audit log
        $this->bankingProvider->giveDebit($operator, $sum);
        $this->bankingProvider->transferDebit($operator->getName(), $targetPlayer->getName(), $sum);
    }

    private function notifyTargetPlayer(FlatParkPlayer $targetPlayer, float $sum)
    {
        $targetPlayer->sendLocalizedMessage("{CommandMoneyGiftMessage1} $sum \n{CommandMoneyGiftMessage2}");
    }

    private function notifyOperators(FlatParkPlayer $operator, FlatParkPlayer $targetPlayer, float $sum)
    {
        $operatorName = $operator->getName();
        $targetPlayerName = $targetPlayer->getName();

        foreach($this->getServer()->getOnlinePlayers() as $player) {
            $player = FlatParkPlayer::cast($player);

            if($player->isOperator()) {
                $player->sendLocalizedMessage("{CommandMoneyGiftNotification} [$sum] $operatorName -> $targetPlayerName");
            }
        }
    }
}