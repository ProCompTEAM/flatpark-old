<?php
namespace flatpark\commands\base;

use flatpark\common\player\FlatParkPlayer;
use flatpark\defaults\PlayerAttributes;

abstract class OrganisationsCommand extends Command
{
    protected function isBoss(FlatParkPlayer $player) : bool
    {
        return $player->existsAttribute(PlayerAttributes::BOSS);
    }
}