<?php
namespace flatpark\commands;

use pocketmine\event\Event;
use flatpark\defaults\Permissions;

use flatpark\commands\base\Command;
use flatpark\common\player\FlatParkPlayer;

class DonateCommand extends Command
{
    public const CURRENT_COMMAND = "donate";

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

    public function getFileSource() : string
    {
        return $this->getCore()->getTargetDirectory() . "strings/donate.txt";
    }

    public function execute(FlatParkPlayer $player, array $args = array(), Event $event = null)
    {
        $file = $this->getFileSource();
        $content = file_exists($file) ? $this->clearData(file_get_contents($file)) : "*";
        $player->sendMessage($content);
    }

    private function clearData($str) : string
    {
        $lines = explode("\r",$str);
        $result = array();
        
        foreach($lines as $line)
        {
            array_push($result, mb_strcut($line, 2));
        }
        
        return implode("\n", $result);
    }
}