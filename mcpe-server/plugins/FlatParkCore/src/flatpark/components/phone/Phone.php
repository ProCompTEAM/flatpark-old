<?php
namespace flatpark\components\phone;

use Exception;
use flatpark\Tasks;
use flatpark\Events;
use flatpark\Providers;
use flatpark\Components;
use pocketmine\world\Position;
use flatpark\components\map\ATM;
use flatpark\defaults\EventList;
use flatpark\defaults\Sounds;
use jojoe77777\FormAPI\SimpleForm;
use flatpark\components\chat\Chat;
use flatpark\defaults\MapConstants;
use flatpark\providers\MapProvider;
use flatpark\defaults\TimeConstants;
use flatpark\components\base\Component;
use flatpark\common\player\FlatParkPlayer;
use flatpark\defaults\ComponentAttributes;
use flatpark\defaults\OrganisationConstants;
use pocketmine\event\player\PlayerQuitEvent;
use flatpark\providers\data\PhonesDataProvider;
use flatpark\components\organisations\Organisations;

class Phone extends Component
{
    private const MAX_STREAM_DISTANCE = 200;

    private const EMERGENCY_NUMBER_POLICE = 02;

    private const EMERGENCY_NUMBER_AMBULANCE = 03;

    private PhonesDataProvider $phonesDataProvider;

    private MapProvider $mapProvider;

    private Chat $chat;

    private ATM $atm;

    public function initialize()
    {
        $this->phonesDataProvider = Providers::getPhonesDataProvider();

        $this->mapProvider = Providers::getMapProvider();

        $this->chat = Components::getComponent(Chat::class);

        $this->atm = Components::getComponent(ATM::class);

        Events::registerEvent(EventList::PLAYER_QUIT_EVENT, [$this, "playerQuitEvent"]);
        Tasks::registerRepeatingAction(TimeConstants::PHONE_TAKE_FEE_INTERVAL, [$this, "takeFee"]);
    }

    public function getAttributes() : array
    {
        return [
            ComponentAttributes::SHARED
        ];
    }

    public function playerQuitEvent(PlayerQuitEvent $event)
    {
        $player = FlatParkPlayer::cast($event->getPlayer());

        if(isset($player->getStatesMap()->phoneOutcomingCall)) {
            $player->getStatesMap()->phoneOutcomingCall->getStatesMap()->phoneIncomingCall = null;
        }

        if(isset($player->getStatesMap()->phoneIncomingCall)) {
            $player->getStatesMap()->phoneIncomingCall->getStatesMap()->phoneOutcomingCall = null;
        }

        if(isset($player->getStatesMap()->phoneCompanion)) {
            $this->breakCall($player->getStatesMap()->phoneCompanion);
        }
    }

    public function takeFee()
    {
        foreach($this->getServer()->getOnlinePlayers() as $player) {
            $player = FlatParkPlayer::cast($player);

            if(!isset($player->getStatesMap()->phoneCompanion)) {
                continue;
            }

            if(!$this->hasStream($player->getStatesMap()->phoneCompanion->getPosition())) {
                $this->breakCallForNoStream($player);
            } elseif(!$this->reduceBalance($player, 20)) {
                $this->breakCallForNoMoney($player);
            }
        }
    }

    private function breakCallForNoStream(FlatParkPlayer $player)
    {
        $player->getStatesMap()->phoneCompanion->sendSound(Sounds::PHONE_LOST_CONNECT);
        $player->sendMessage("PhoneNoNet");
        $player->sendMessage("PhoneErrorNet");

        $player->getStatesMap()->phoneCompanion->sendMessage("PhoneErrorNet");

        $player->getStatesMap()->phoneCompanion->getStatesMap()->phoneCompanion = null; 
        $player->getStatesMap()->phoneCompanion = null;
    }

    private function breakCallForNoMoney(FlatParkPlayer $player)
    {
        $player->getStatesMap()->phoneCompanion->sendSound(Sounds::PHONE_LOST_CONNECT);
        $player->sendMessage("PhoneContinueNoMoney");
        $player->sendMessage("PhoneErrorNet");

        $player->getStatesMap()->phoneCompanion->sendMessage("PhoneErrorNet");

        $player->getStatesMap()->phoneCompanion->getStatesMap()->phoneCompanion = null; 
        $player->getStatesMap()->phoneCompanion = null;
    }

    public function getPlayerByNumber(int $number) : ?FlatParkPlayer
    {
        $userName = $this->phonesDataProvider->getUserNameByNumber($number);

        if(isset($userName)) {
            return $this->getServer()->getPlayerByPrefix($userName);
        }

        return null;
    }

    public function getPlayerNameByNumber(int $number) : ?string
    {
        return $this->phonesDataProvider->getUserNameByNumber($number);
    }

    public function getNumberByName(string $userName) : ?int
    {
        return $this->phonesDataProvider->getNumberForUser($userName);
    }

    public function hasStream(Position $position)
    {
        return $this->mapProvider->hasNearPointWithType($position, self::MAX_STREAM_DISTANCE, MapConstants::POINT_GROUP_STREAM);
    }

    public function initializeCallRequest(FlatParkPlayer $initializer, int $targetNumber)
    {
        if($this->checkForEmergencyNumber($initializer, $targetNumber)) {
            return;
        }

        if(!$this->hasStream($initializer->getPosition())) {
            $initializer->sendSound(Sounds::PHONE_DISCONNECT);
            $initializer->sendMessage("PhoneNoNet2");
            return;
        }

        $target = $this->getPlayerByNumber($targetNumber);

        if(!isset($target)) {
            $initializer->sendSound(Sounds::PHONE_DISCONNECT);
            $initializer->sendMessage("PhoneNoNet");
            return;
        }

        if($target->getName() === $initializer->getName()) {
            $initializer->sendSound(Sounds::PHONE_CHECK_NUM);
            $initializer->sendMessage("PhoneCheckNum");
            return;
        }

        $initializer->sendSound(Sounds::PHONE_UNDIALING);
        $initializer->sendMessage("PhoneBeeps");

        if(isset($initializer->getStatesMap()->phoneCompanion) or isset($initializer->getStatesMap()->phoneIncomingCall)
            or isset($initializer->getStatesMap()->phoneOutcomingCall)) {
            $initializer->sendSound(Sounds::PHONE_UNAVAILABLE);
            $initializer->sendMessage("PhoneAlreadyInCall");
            return;
        }

        if(isset($target->getStatesMap()->phoneCompanion) or isset($target->getStatesMap()->phoneIncomingCall)
            or isset($target->getStatesMap()->phoneOutcomingCall)) {
            $initializer->sendSound(Sounds::PHONE_UNDIALING);
            $initializer->sendMessage("PhoneCalling3");
            return;
        }

        $target->getStatesMap()->phoneIncomingCall = $initializer;
        $initializer->getStatesMap()->phoneOutcomingCall = $target;

        $this->chat->sendLocalMessage($target, "{PhoneCallingBeep}", "§d : ", 10);

        $target->sendSound(Sounds::PHONE_INCOMING_CALL);
        $target->sendLocalizedMessage("{PhoneCalling1}" . $initializer->getProfile()->phoneNumber . ".");
        $target->sendMessage("PhoneCalling2");
        $target->sendMessage("PhoneCalling4");

        $initializer->sendSound(Sounds::PHONE_OUTCOMMING_CALL);
        $initializer->sendMessage("PhoneCalling5");
    }

    public function sendSms(FlatParkPlayer $sender, int $targetNumber, string $text)
    {
        if(!$this->hasStream($sender->getPosition())) {
            $sender->sendMessage("PhoneSmsError");
            return;
        }

        $target = $this->getPlayerByNumber($targetNumber);

        if(!isset($target)) {
            $sender->sendMessage("PhoneSmsNoNet");
            return;
        }

        if($target->getName() === $sender->getName()) {
            $sender->sendMessage("PhoneCheckNum");
            return;
        }

        if(!$this->reduceBalance($sender, 20)) {
            $sender->sendMessage("PhoneSmsNoMoney");
            return;
        }

        $target->sendSound(Sounds::PHONE_SMS);
        $target->sendLocalizedMessage("{PhoneSend}" . $sender->getProfile()->phoneNumber);
        $target->sendMessage("§b[➪] " . $text);

        $sender->sendMessage("PhoneSmsSuccess");
    }

    public function acceptOrEndCall(FlatParkPlayer $player, string $method)
    {
        if($method === "accept" and isset($player->getStatesMap()->phoneIncomingCall)) {
            $this->acceptCall($player);
        } elseif($method === "end" and isset($player->getStatesMap()->phoneCompanion)) {
            $this->endCall($player);
        } elseif($method === "cancel" and isset($player->getStatesMap()->phoneOutcomingCall)) {
            $this->cancelRequest($player);
        } elseif($method === "reject" and isset($player->getStatesMap()->phoneIncomingCall)) {
            $this->rejectCall($player);
        } else {
            $player->sendMessage("PhoneCallReload");
        }
    }

    public function sendDisplayMessages(FlatParkPlayer $player)
    {
        $message  = "§9☏ Позвонить: §e/c <номер телефона>\n";
        $message .= "§9☏ Служба Охраны: §e/c 02\n";
        $message .= "§9☏ Мед. помощь: §e/c 03\n";
        $message .= "§9☏ Сообщения: §e/sms <н.телефона> <текст>\n";
        $message .= "§1> Цены: §aСМС 20р, Звонок 20р минута\n";
        $message .= "§1> Ваш телефонный номер: §3" . $player->getProfile()->phoneNumber . "\n";
        $message .= "§1> Ваш баланс: §3" . $this->getBalance($player) . "р\n\n";

        $form = new SimpleForm([$this, "phoneMenuForm"]);
        $form->setTitle("§9❖======*Смартфон*=======❖");
        $form->setContent($message);
        $form->addButton("Интернет-банкинг", -1, "", "banking");

        $player->sendForm($form);
    }

    public function phoneMenuForm(FlatParkPlayer $player, $data = null)
    {
        if($data == "banking") {
            $this->atm->sendMoneyInfo($player);
        }
    }

    public function handleMessage(FlatParkPlayer $player, string $message)
    {
        $number = $player->getProfile()->phoneNumber;

        $player->getStatesMap()->phoneCompanion->sendMessage("§9✆ §e$number §6: §a".$message);
        $player->sendMessage("§9✆ §5$number §6: §2".$message);
    }

    public function getBalance(FlatParkPlayer $player) : float
    {
        return $this->phonesDataProvider->getBalance($player->getName());
    }

    public function addBalance(FlatParkPlayer $player, float $amount) : bool
    {
        return $this->phonesDataProvider->addBalance($player->getName(), $amount);
    }

    public function reduceBalance(FlatParkPlayer $player, float $amount) : bool
    {
        return $this->phonesDataProvider->reduceBalance($player->getName(), $amount);
    }

    private function acceptCall(FlatParkPlayer $player)
    {
        $target = $player->getStatesMap()->phoneIncomingCall;

        if(!isset($target->getStatesMap()->phoneOutcomingCall)) {
            throw new Exception("Target doesn't have property");
        }

        $player->sendLocalizedMessage("{PhoneCall1}" . $target->getProfile()->phoneNumber . ".."); 
        $player->sendMessage("PhoneCall2");
        $player->sendMessage("PhoneCall3");

        $target->sendLocalizedMessage("{PhoneCall1}" . $player->getProfile()->phoneNumber . ".."); 
        $target->sendMessage("PhoneCall2");
        $target->sendMessage("PhoneCall3");

        $player->getStatesMap()->phoneIncomingCall = null;
        $target->getStatesMap()->phoneOutcomingCall = null;

        $player->getStatesMap()->phoneCompanion = $target;
        $target->getStatesMap()->phoneCompanion = $player;
    }
    
    private function rejectCall(FlatParkPlayer $player)
    {
        $player->getStatesMap()->phoneIncomingCall->sendMessage("PhoneCallRejected");

        $player->getStatesMap()->phoneIncomingCall->getStatesMap()->phoneOutcomingCall = null;
        $player->getStatesMap()->phoneIncomingCall = null;
    }

    private function endCall(FlatParkPlayer $player)
    {
        $player->sendMessage("PhoneCallEnd");
        $player->getStatesMap()->phoneCompanion->sendMessage("PhoneCallEnd");

        $player->getStatesMap()->phoneCompanion->getStatesMap()->phoneCompanion = null;
        $player->getStatesMap()->phoneCompanion = null;
    }

    private function cancelRequest(FlatParkPlayer $player)
    {
        $player->getStatesMap()->phoneOutcomingCall->sendSound(Sounds::PHONE_UNDIALING);
        $player->sendMessage("PhoneCancelRequest");

        $player->getStatesMap()->phoneOutcomingCall->getStatesMap()->phoneIncomingCall = null;
        $player->getStatesMap()->phoneOutcomingCall = null;
    }

    public function breakCall(FlatParkPlayer $player)
    {
        $player->sendSound(Sounds::PHONE_LOST_CONNECT);
        $player->getStatesMap()->phoneCompanion = null;
        $player->sendMessage("PhoneErrorNet");
    }

    private function checkForEmergencyNumber(FlatParkPlayer $initializer, int $number) : bool
    {
        $organisationId = null;

        if($number === self::EMERGENCY_NUMBER_POLICE) {
            $organisationId = OrganisationConstants::SECURITY_WORK;
        } elseif($number === self::EMERGENCY_NUMBER_AMBULANCE) {
            $organisationId = OrganisationConstants::DOCTOR_WORK;
        }

        if(!isset($organisationId)) {
            return false;
        }

        $this->makeEmergencyCall($initializer, $organisationId);

        return true;
    }

    private function makeEmergencyCall(FlatParkPlayer $player, int $organisationId)
    {
        $messages = $this->generateEmergencyMessages($player);

        foreach($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
            $onlinePlayer = FlatParkPlayer::cast($onlinePlayer);

            if($onlinePlayer->getSettings()->organisation !== $organisationId) {
                continue;
            }

            foreach($messages as $message) {
                $onlinePlayer->sendLocalizedMessage($message);
            }
        }

        $player->sendMessage("PhoneEventCallHelp1");
        $player->sendMessage("PhoneEventCallHelp2");
        $player->sendMessage("PhoneEventCallHelp3");
        $player->sendMessage("PhoneEventCallHelp4");
    }

    private function generateEmergencyMessages(FlatParkPlayer $player) : array
    {
        $nearPoints = $this->mapProvider->getNearPoints($player->getPosition(), 15);

        $messages = [];

        if(!isset($nearPoints[0])) {
            $messages[] = "{PhoneEvent1}";
        } else {
            $messages[] = "{PhoneEvent2}";
        }

        $messages[] = "{PhoneEvent3}" . $player->getProfile()->phoneNumber;
        $messages[] = "{PhoneEvent4}" . $player->getProfile()->fullName;
        $messages[] = "{PhoneEvent5}" . implode(", ", $player->property);

        if(!isset($nearPoints[0])) {
            $messages[] = "{PhoneEvent6}";
        } else {
            $messages[] = "{PhoneEvent7}" . $nearPoints[0];
        }

        return $messages;
    }
}