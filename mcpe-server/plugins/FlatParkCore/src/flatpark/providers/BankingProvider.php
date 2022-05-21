<?php
namespace flatpark\providers;

use flatpark\providers\base\Provider;
use flatpark\common\player\FlatParkPlayer;
use flatpark\defaults\PaymentMethods;
use flatpark\models\dtos\PaymentMethodDto;
use flatpark\Providers;
use flatpark\providers\data\BankingDataProvider;

class BankingProvider extends Provider
{
    private const PREFIX = "[BANK] ";

    private BankingDataProvider $bankingDataProvider;

    public function __construct()
    {
        $this->bankingDataProvider = Providers::getBankingDataProvider();
    }
    
    public function getPlayerMoney(FlatParkPlayer $player) : float
    {
        switch($player->getStatesMap()->paymentMethod) {
            case PaymentMethods::CASH:
                return $this->getCash($player);
            case PaymentMethods::DEBIT:
                return $this->getDebit($player);
            case PaymentMethods::CREDIT:
                return $this->getCredit($player);
        }
    }
    
    public function takePlayerMoney(FlatParkPlayer $player, float $money, bool $label = true) : bool
    {
        $status = false;

        switch($player->getStatesMap()->paymentMethod) {
            case PaymentMethods::CASH:
                $status = $this->reduceCash($player, $money);
            break;
            case PaymentMethods::DEBIT:
                $status = $this->reduceDebit($player, $money);
            break;
            case PaymentMethods::CREDIT:
                $status = $this->reduceCredit($player, $money);
            break;
        }
    
        if ($label and $status) {
            $player->sendLocalizedMessage(self::PREFIX . "{BankingDebiting}" . $money);
            $player->sendLocalizedMessage(self::PREFIX . "{BankingBalance}" . $this->getPlayerMoney($player) . "{Rub}");
        }
        
        return $status;
    }
    
    public function givePlayerMoney(FlatParkPlayer $player, float $money, bool $label = true) : bool
    { 
        $status = false;

        switch($player->getStatesMap()->paymentMethod) {
            case PaymentMethods::CASH:
                $status = $this->giveCash($player, $money);
            break;
            case PaymentMethods::DEBIT:
                $status = $this->giveDebit($player, $money);
            break;
            case PaymentMethods::CREDIT:
                $status = $this->giveCredit($player, $money);
            break;
        }
        
        if ($label and $status) {
            $player->sendLocalizedMessage(self::PREFIX . "{BankingCrediting}" . $money);
            $player->sendLocalizedMessage(self::PREFIX . "{BankingBalance}" . $this->getPlayerMoney($player) . "{Rub}");
        }
        
        return $status;
    }

    public function getCash(FlatParkPlayer $player) : float
    {
        return $this->bankingDataProvider->getCash($player->getName());
    }

    public function getDebit(FlatParkPlayer $player) : float
    {
        return $this->bankingDataProvider->getDebit($player->getName());
    }

    public function getCredit(FlatParkPlayer $player) : float
    {
        return $this->bankingDataProvider->getCredit($player->getName());
    }

    public function getAllMoney(FlatParkPlayer $player) : float
    {
        return $this->bankingDataProvider->getAllMoney($player->getName());
    }

    public function giveCash(FlatParkPlayer $player, float $amount) : bool
    {
        return $this->bankingDataProvider->giveCash($player->getName(), $amount);
    }

    public function giveDebit(FlatParkPlayer $player, float $amount) : bool
    {
        return $this->bankingDataProvider->giveDebit($player->getName(), $amount);
    }

    public function giveCredit(FlatParkPlayer $player, float $amount) : bool
    {
        return $this->bankingDataProvider->giveCredit($player->getName(), $amount);
    }

    public function reduceCash(FlatParkPlayer $player, float $amount) : bool
    {
        return $this->bankingDataProvider->reduceCash($player->getName(), $amount);
    }

    public function reduceDebit(FlatParkPlayer $player, float $amount) : bool
    {
        return $this->bankingDataProvider->reduceDebit($player->getName(), $amount);
    }

    public function reduceCredit(FlatParkPlayer $player, float $amount) : bool
    {
        return $this->bankingDataProvider->reduceCredit($player->getName(), $amount);
    }

    public function transferDebit(string $userName, string $target, float $amount) : bool
    {
        return $this->bankingDataProvider->transferDebit($userName, $target, $amount);
    }

    public function getPaymentMethod(FlatParkPlayer $player) : int
    {
        return $this->bankingDataProvider->getPaymentMethod($player->getName());
    }

    public function switchPaymentMethod(FlatParkPlayer $player, int $method) : bool
    {
        $dto = $this->getPaymentMethodDto($player->getName(), $method);

        $status = $this->bankingDataProvider->switchPaymentMethod($dto);

        if ($status) {
            $player->getStatesMap()->paymentMethod = $method;
        }

        return $status;
    }

    public function initializePlayerPaymentMethod(FlatParkPlayer $player)
    {
        $player->getStatesMap()->paymentMethod = $this->bankingDataProvider->getPaymentMethod($player->getName());
    }

    private function getPaymentMethodDto(string $userName, int $method) : PaymentMethodDto
    {
        $dto = new PaymentMethodDto;
        $dto->name = $userName;
        $dto->method = $method;
        return $dto;
    }
}