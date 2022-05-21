<?php
namespace flatpark\commands\report;

use flatpark\common\player\FlatParkPlayer;
use pocketmine\event\Event;

use flatpark\defaults\Permissions;
use flatpark\commands\base\Command;
use flatpark\Components;
use flatpark\components\administrative\Reports;

class CloseCommand extends Command
{
    public const CURRENT_COMMAND = "close";

    private Reports $reports;

    public function __construct()
    {
        $this->reports = Components::getComponent(Reports::class);
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
        if (self::argumentsNo($args)) {
            $player->sendMessage("NoArguments");
            return;
        }
            
        $response = $this->reports->closeReport(intval($args[0]));

        if (!$response) {
            $player->sendMessage("ReportCloseNoID");
        }
    }
}