<?php
namespace flatpark\commands\report;

use flatpark\Components;
use pocketmine\event\Event;

use flatpark\components\administrative\Reports;

use flatpark\defaults\Permissions;
use flatpark\commands\base\Command;
use flatpark\common\player\FlatParkPlayer;

class ReportCommand extends Command
{
    public const CURRENT_COMMAND = "report";

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
            Permissions::ANYBODY
        ];
    }

    public function execute(FlatParkPlayer $player, array $args = array(), Event $event = null)
    {
        if (self::argumentsNo($args)) {
            $player->sendMessage("NoArguments2");
            return;
        }

        $reportMessage = implode(" ", $args);
        
        $this->reports->playerReport($player, $reportMessage);
    }
}