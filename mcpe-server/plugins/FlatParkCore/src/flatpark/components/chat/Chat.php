<?php
namespace flatpark\components\chat;

use flatpark\Events;
use flatpark\Providers;
use flatpark\Components;
use flatpark\components\phone\Phone;
use flatpark\defaults\EventList;
use flatpark\components\administrative\Tracking;
use flatpark\defaults\ChatConstants;
use flatpark\components\base\Component;
use flatpark\common\player\FlatParkPlayer;
use flatpark\defaults\ComponentAttributes;
use pocketmine\event\player\PlayerChatEvent;

class Chat extends Component
{
    private const CHAT_MESSAGE_PREFIX = "{ChatSaid}";

    private const ROLEPLAY_ACTION_PREFIX = "§d";

    private const SELF_CHAT_PREFIX = "{ChatIAm}";

    private Phone $phone;
    private Tracking $tracking;

    public function initialize()
    {
        Events::registerEvent(EventList::PLAYER_CHAT_EVENT, [$this, "executeInputData"]);

        $this->phone = Components::getComponent(Phone::class);
        $this->tracking = Components::getComponent(Tracking::class);
    }

    public function getAttributes(): array
    {
        return [
            ComponentAttributes::SHARED
        ];
    }

    public function executeInputData(PlayerChatEvent $event)
    {
        $event->cancel();

        $player = FlatParkPlayer::cast($event->getPlayer());

        if(!$player->isAuthorized()) {
            return;
        }

        if ($player->muted) {
            $player->sendMessage("ChatMute");
            return;
        }

        $message = $event->getMessage();

        if (isset($player->getStatesMap()->phoneCompanion)) {
            $this->handleInCallMessage($player, $message);
            return;
        }

        $signature = $message[0];

        if ($signature === ChatConstants::GLOBAL_CHAT_SIGNATURE) {
            $this->sendGlobalMessage($player, substr($message, 1));
        } else if ($signature === ChatConstants::ADMINISTRATION_CHAT_SIGNATURE) {
            $this->sendAdminMessage($player, substr($message, 1));
        } else {
            $this->tracking->message($player, $message, ChatConstants::LOCAL_CHAT_HEAR_RADIUS, "§b[Local Chat]");
            $this->sendLocalMessage($player, $message, self::CHAT_MESSAGE_PREFIX, ChatConstants::LOCAL_CHAT_HEAR_RADIUS, true);
        }
    }

    public function sendLocalMessage(FlatParkPlayer $player, string $message, string $prefix = self::CHAT_MESSAGE_PREFIX, int $radius = ChatConstants::LOCAL_CHAT_HEAR_RADIUS, bool $checkForEmotions = false)
    {
        $senderFullName = $player->getProfile()->fullName;

        if ($checkForEmotions and $this->checkForEmotion($player, $message)) {
            return;
        }

        if ($checkForEmotions and $this->checkMessageSuffix($player, $message)) {
            return;
        }

        $isFriendRequest = $this->isFriendsRequest($senderFullName, $message);

        $randomPrefix = "§7" . $this->getRandomUserPrefix();

        foreach ($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
            if ($onlinePlayer->getLocation()->distance($player->getLocation()) <= $radius) {
                $this->sendMessage($onlinePlayer, $message, strtolower($player->getName()), $senderFullName, $isFriendRequest, $randomPrefix, $prefix);
            }
        }
    }

    public function sendGlobalMessage(FlatParkPlayer $player, string $message)
    {
        if ($player->getStatesMap()->isBeginner) {
            return $player->sendMessage("ChatRestrictBeginner");
        }

        if (!$this->phone->hasStream($player->getPosition())) {
            return $player->sendMessage("ChatNoStream");
        }

        $generatedMessage = "{GlobalMessagePart1}" . $player->getProfile()->fullName . " {GlobalMessagePart2}$message";

        foreach ($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
            $onlinePlayer = FlatParkPlayer::cast($onlinePlayer);

            if ($this->phone->hasStream($onlinePlayer->getPosition())) {
                $onlinePlayer->sendLocalizedMessage($generatedMessage);
            }
        }
    }

    public function sendAdminMessage(FlatParkPlayer $player, string $message)
    {
        if (!$player->isAdministrator()) {
            return $player->sendMessage("ChatRestrictAdmin");
        }

        $generatedMessage = "{AdminChatPart1}" . $player->getProfile()->fullName . " {AdminChatPart2}$message";

        foreach ($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
            $onlinePlayer = FlatParkPlayer::cast($onlinePlayer);

            if ($onlinePlayer->isAdministrator()) {
                $onlinePlayer->sendLocalizedMessage($generatedMessage);
            }
        }
    }

    private function handleInCallMessage(FlatParkPlayer $player, string $message)
    {
        $this->phone->handleMessage($player, $message);

        $this->sendLocalMessage($player, $message, "{ChatSpeakPhone}");
        
        $this->tracking->message($player, $message, 7, "[PHONE]");
    }

    private function sendMessage(FlatParkPlayer $targetPlayer, string $message, string $senderName, string $senderFullName, bool $haveToBeFriends, string $userPrefix, string $chatPrefix)
    {
        if (strtolower($targetPlayer->getName()) === $senderName) {
            $userPrefix = self::SELF_CHAT_PREFIX;
        } else if (str_contains($targetPlayer->getProfile()->people, $senderName)) {
            $userPrefix = "§7" . $senderFullName;
        } else if ($haveToBeFriends) {
            $userPrefix = "§2" . $senderFullName;

            $targetPlayer->getProfile()->people .= $senderName;

            Providers::getProfileProvider()->saveProfile($targetPlayer);
        }

        $targetPlayer->sendLocalizedMessage($userPrefix . $chatPrefix . " $message");
    }

    private function isFriendsRequest(string $personFullName, string $message) : bool
    {
        $message = mb_strtolower($message);

        foreach ($this->getFriendRequestDictionary($personFullName) as $requestWord) {
            if (str_contains($message, $requestWord)) {
                return true;
            }
        }

        return false;
    }

    private function checkForEmotion(FlatParkPlayer $player, string $message) : bool
    {
        if (!isset($this->getEmotionActions()[$message])) {
            return false;
        }

        $emotion = $this->getEmotionActions()[$message];

        $this->sendLocalMessage($player, $emotion, self::ROLEPLAY_ACTION_PREFIX, ChatConstants::ROLEPLAY_ACTION_RADIUS);

        return true;
    }

    private function checkMessageSuffix(FlatParkPlayer $player, string $message) : bool
    {
        $suffix = $message[-1];

        if (!isset($this->getEmotionEndingPrefixes()[$suffix])) {
            return false;
        }

        $chatPrefix = $this->getEmotionEndingPrefixes()[$suffix];

        $this->sendLocalMessage($player, substr($message, 0, strlen($message) - 1), $chatPrefix);

        return true;
    }

    private function getRandomUserPrefix()
    {
        return "{ChatUserPrefix" . mt_rand(1, $this->getUserChatPrefixesCount()) . "}";
    }

    private function getUserChatPrefixesCount() : int
    {
        return 11;
    }

    private function getFriendRequestDictionary(string $personName) : array
    {
        return [
            "мое имя", "меня зовут", "звать меня", "my name","мене звати", "mam na imię",
            "mano vardas", "ich heiße", "mon nom est", "Менің атым", "mi chiamo", strtolower($personName)
        ];
    }

    private function getEmotionEndingPrefixes() : array
    {
        return [
            ")" => "{ChatSaidWithSmile}",
            "(" => "{ChatSaidWithSad}"
        ];
    }

    private function getEmotionActions() : array
    {
        return [
            ":)" => "{ChatSmile}",
            ":(" => "{ChatSad}",
            ":/" => "{ChatSurprise}",
            ":D" => "{ChatLaugh}",
        ];
    }
}