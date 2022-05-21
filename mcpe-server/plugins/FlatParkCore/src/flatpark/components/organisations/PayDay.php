<?php
namespace flatpark\components\organisations;

use flatpark\Tasks;
use flatpark\Providers;
use flatpark\defaults\TimeConstants;
use flatpark\defaults\PayDayConstants;
use flatpark\components\base\Component;
use flatpark\defaults\PlayerAttributes;
use flatpark\providers\BankingProvider;
use flatpark\common\player\FlatParkPlayer;
use flatpark\defaults\ComponentAttributes;
use flatpark\defaults\OrganisationConstants;
use flatpark\components\organisations\Organisations;

class PayDay extends Component
{
    private BankingProvider $bankingProvider;

    public function initialize()
    {
        Tasks::registerRepeatingAction(TimeConstants::PAYDAY_INTERVAL, [$this, "calculateSalary"]);

        $this->bankingProvider = Providers::getBankingProvider();
    }

    public function getAttributes() : array
    {
        return [
            ComponentAttributes::STANDALONE
        ];
    }
    
    public function calculateSalary()
    {
        foreach($this->getServer()->getOnlinePlayers() as $player) {
            $player = FlatParkPlayer::cast($player);

            if(!$player->isAuthorized()) {
                continue;
            }

            $salary = $this->getSalaryValue($player); 
            $special = 0;

            if($player->getStatesMap()->isNew) {
                $special += PayDayConstants::NEW_PLAYER_SPECIAL;
            }

            if($player->getSettings()->organisation == OrganisationConstants::NO_WORK) {
                $special += PayDayConstants::WORKLESS_SPECIAL;
            }

            if($player->isAdministrator()) {
                $special += PayDayConstants::ADMINISTRATOR_SPECIAL;
            }

            if($player->existsAttribute(PlayerAttributes::BOSS)) {
                $salary *= 2;
            }

            $summary = $salary + $special;

            if($summary > 0) {
                $this->bankingProvider->giveDebit($player, $summary);
            }

            if($summary < 0) {
               $this->bankingProvider->reduceDebit($player, $summary);
            }

            $this->sendForm($player, $salary, $special, $summary);
        }
    }

    private function sendForm(FlatParkPlayer $player, int $salary, int $special, int $summary)
    {
        $form = "§7----=====§eВРЕМЯ ЗАРПЛАТЫ§7=====----";
        $form .= "\n §3> §fЗаработано: §2" . $salary;
        $form .= "\n §3> §fПособие: §2" . $special;
        $form .= "\n§8- - - -== -==- ==- - - -";
        $form .= "\n §3☛ §fИтого: §2" . $summary;

        $player->sendMessage($form);
    }

    private function getSalaryValue(FlatParkPlayer $player) : int
    {
        switch($player->getSettings()->organisation) {
            case OrganisationConstants::TAXI_WORK:
                return PayDayConstants::TAXI_WORK_SALARY;
            case OrganisationConstants::DOCTOR_WORK:
                return PayDayConstants::DOCTOR_WORK_SALARY;
            case OrganisationConstants::LAWYER_WORK:
                return PayDayConstants::LAWYER_WORK_SALARY;
            case OrganisationConstants::SECURITY_WORK:
                return PayDayConstants::SECURITY_WORK_SALARY;
            case OrganisationConstants::SELLER_WORK:
                return PayDayConstants::SELLER_WORK_SALARY;
            case OrganisationConstants::GOVERNMENT_WORK:
                return PayDayConstants::GOVERNMENT_WORK_SALARY;
            case OrganisationConstants::EMERGENCY_WORK:
                return PayDayConstants::EMERGENCY_WORK_SALARY;
        }

        return 0;
    }
}