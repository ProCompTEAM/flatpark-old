<?php
namespace flatpark\commands\map;

use flatpark\commands\base\Command;
use flatpark\common\player\FlatParkPlayer;
use flatpark\Components;
use flatpark\components\map\FloatingTexts;
use flatpark\defaults\Permissions;
use pocketmine\event\Event;

class FloatingTextsCommand extends Command
{
    private const NAME = "floatingtexts";

    private const CHOICE_CREATE = 0;
    
    private const CHOICE_REMOVE = 1;

    private FloatingTexts $floatingTexts;

    public function getCommand(): array
    {
        return [
            self::NAME
        ];
    }

    public function __construct()
    {
        $this->floatingTexts = Components::getComponent(FloatingTexts::class);
    }

    public function getPermissions(): array
    {
        return [
            Permissions::ADMINISTRATOR
        ];
    }

    public function execute(FlatParkPlayer $player, array $args = array(), ?Event $event = null)
    {
        $this->floatingTexts->initializeMenu($player);
    }
}