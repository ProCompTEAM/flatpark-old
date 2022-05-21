<?php
namespace flatpark\components\organisations;

use flatpark\defaults\TimeConstants;
use flatpark\Providers;
use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\world\Position;

use pocketmine\entity\effect\EffectInstance;
use flatpark\components\base\Component;
use flatpark\common\player\FlatParkPlayer;
use flatpark\Components;
use flatpark\components\chat\Chat;
use flatpark\defaults\ComponentAttributes;
use flatpark\providers\BankingProvider;
use flatpark\providers\MapProvider;

class Farm extends Component
{
    public const POINT_NAME = "Ферма";

    private BankingProvider $bankingProvider;

    private MapProvider $mapProvider;

    private Chat $chat;

    public function initialize()
    {
        $this->bankingProvider = Providers::getBankingProvider();

        $this->mapProvider = Providers::getMapProvider();

        $this->chat = Components::getComponent(Chat::class);
    }

    public function getAttributes() : array
    {
        return [
            ComponentAttributes::STANDALONE
        ];
    }

    public function getHarvest(FlatParkPlayer $player)
    {
        if(!$this->isPlayerNearWheat($player)) {
            $player->sendMessage("FarmNoNear");
            return;
        }

        $this->giveSlownessEffect($player);

        $this->chat->sendLocalMessage($player, "{FarmHarvestIn}", "§d : ", 12);
        $player->getStatesMap()->bar = "§eДонесите корзину на пункт сбора около фермы";
        $player->getStatesMap()->loadWeight = 1;
    }

    public function putHarvest(FlatParkPlayer $player)
    {
        if(!$this->isPlayerAtFarm($player)) {
            $player->sendMessage("FarmHarvestOutNoNear");
            return;
        }

        isset($player->getStatesMap()->loadWeight) ? $this->handleDrop($player) : $player->sendMessage("FarmNoHarvest");
    }

    private function giveSlownessEffect(FlatParkPlayer $player)
    {
        $effect = VanillaEffects::fromString("slowness");
        $instance = new EffectInstance($effect, TimeConstants::ONE_SECOND_TICKS * 9999, 1, true);
        $player->getEffects()->add($instance);
    }
    
    private function handleDrop(FlatParkPlayer $player)
    {
        $player->getEffects()->clear();

        $this->chat->sendLocalMessage($player, "{FarmHarvestOut}", "§d ", 12);
        $this->bankingProvider->givePlayerMoney($player, 150);

        $player->getStatesMap()->loadWeight = null; 
        $player->getStatesMap()->bar = null;
    }

    private function isPlayerAtFarm(FlatParkPlayer $player) : bool
    {
        $points = $this->mapProvider->getNearPoints($player->getPosition(), 3);

        return in_array(self::POINT_NAME, $points);
    }

    private function isPlayerNearWheat(FlatParkPlayer $player) : bool
    {
        $vector = $player->getLocation()->subtract(0, 1, 0);
        $block = $player->getWorld()->getBlock($vector);

        return $block->getId() === BlockLegacyIds::FARMLAND;
    }
}