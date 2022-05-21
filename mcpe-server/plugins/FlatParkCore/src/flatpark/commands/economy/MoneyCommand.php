<?php
namespace flatpark\commands\economy;

use flatpark\Components;
use flatpark\components\map\ATM;
use flatpark\Providers;
use pocketmine\event\Event;

use flatpark\defaults\Permissions;
use flatpark\commands\base\Command;
use flatpark\common\player\FlatParkPlayer;
use flatpark\providers\BankingProvider;

class MoneyCommand extends Command
{
    public const CURRENT_COMMAND = "money";
    public const CURRENT_COMMAND_ALIAS = "balance";

    private BankingProvider $bankingProvider;

    private ATM $atm;

    public function __construct()
    {
        $this->bankingProvider = Providers::getBankingProvider();

        $this->atm = Components::getComponent(ATM::class);
    }

    public function getCommand() : array
    {
        return [
            self::CURRENT_COMMAND,
            self::CURRENT_COMMAND_ALIAS
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
        $this->atm->sendMoneyInfo($player);
    }
}