<?php
namespace flatpark\commands\organisations;

use flatpark\Providers;
use flatpark\Components;

use pocketmine\event\Event;
use pocketmine\item\ItemFactory;
use flatpark\components\chat\Chat;
use flatpark\defaults\Permissions;
use flatpark\defaults\MapConstants;
use flatpark\common\player\FlatParkPlayer;
use flatpark\defaults\OrganisationConstants;
use flatpark\commands\base\OrganisationsCommand;
use flatpark\components\organisations\Organisations;

class SellCommand extends OrganisationsCommand
{
    private const CURRENT_COMMAND = "sell";

    private const MARKETPLACE_DISTANCE = 15;

    private Chat $chat;

    public function __construct()
    {
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
        if (!self::isSeller($player)) {
            $player->sendMessage("CommandSellNoSeller");
            return;
        }

        $this->chat->sendLocalMessage($player, "{CommandSellKey}", "§d : ", 10);

        if (!$this->isShopClose($player)) {
            $player->sendMessage("CommandSellNoShop");
            return;
        }

        $buyers = $this->getBuyersNear($player);

        if (self::argumentsNo($buyers)) {
            $player->sendMessage("CommandSellNoCash");
            return;
        }
        
        $this->handleAllBuyers($buyers, $player);
    }

    public static function isSeller(FlatParkPlayer $player) : bool
    {
        return $player->getSettings()->organisation === OrganisationConstants::SELLER_WORK or $player->isOperator();
    }

    private function isShopClose(FlatParkPlayer $player)
    {
        return Providers::getMapProvider()->hasNearPointWithType($player->getPosition(), self::MARKETPLACE_DISTANCE, MapConstants::POINT_GROUP_MARKETPLACE);
    }

    private function getBuyersNear(FlatParkPlayer $player)
    {
        $players = $this->getCore()->getRegionPlayers($player->getPosition(), 7);
        $buyers = [];

        foreach($players as $currentPlayer) {
            if(isset($currentPlayer->getStatesMap()->goods[0])) {
                $buyers[] = $currentPlayer;
            }
        }

        return $buyers;
    }

    private function handleAllBuyers(array $buyers, FlatParkPlayer $seller)
    {
        foreach($buyers as $buyerId => $buyer) {
            $price = 0;

            foreach($buyer->getStatesMap()->goods as $g) {
                $price = $price + $g[1];
            }

            if(Providers::getBankingProvider()->takePlayerMoney($buyer, $price)) {
                $this->handleSell($price, $buyer, $seller, $buyerId);
            } else {
                $this->notMuchMoney($buyer, $seller, $buyerId);
            }
        }
    }

    private function notMuchMoney(FlatParkPlayer $buyer, FlatParkPlayer $seller, int $buyerId)
    {
        $seller->sendLocalizedMessage("CommandSellNoMoney1Part1" . ($buyerId + 1) . "CommandSellNoMoney1Part2");
        $buyer->sendMessage("CommandSellNoMoney2");
        $buyer->sendMessage("CommandSellNoMoney3");
        $buyer->getStatesMap()->goods = [];
    }

    private function handleSell(int $price, FlatParkPlayer $buyer, FlatParkPlayer $seller, int $buyerId)
    {
        $receipt = "§e--==========ЧЕК==========--\n";

        foreach($buyer->getStatesMap()->goods as $good) {
            $item = ItemFactory::getInstance()->get($good[0]);
            $item->setCustomName($good[2]);
            $buyer->getInventory()->addItem($item);
            $receipt .= "§a" . $good[2] . " §eза §3" . $good[1] . " руб\n";
        }

        $buyer->sendMessage($receipt);
        $buyer->sendLocalizedMessage("{CommandSellFinalPart1}" . $price . "{CommandSellFinalPart2}");

        $buyer->getStatesMap()->goods = [];

        if($seller->getName() !== $buyer->getName()) {
            Providers::getBankingProvider()->givePlayerMoney($seller, ceil($price / 2));
        }

        $seller->sendLocalizedMessage("{CommandSellDo}" . ($buyerId + 1));
    }
}