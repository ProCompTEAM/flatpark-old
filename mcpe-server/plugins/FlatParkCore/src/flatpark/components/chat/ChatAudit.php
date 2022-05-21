<?php
namespace flatpark\components\chat;

use flatpark\common\player\FlatParkPlayer;
use flatpark\defaults\ChatConstants;
use flatpark\defaults\EventList;
use flatpark\Events;
use flatpark\components\base\Component;
use flatpark\Providers;
use flatpark\providers\data\UsersDataProvider;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

class ChatAudit extends Component
{
    private UsersDataProvider $usersProvider;

    public function initialize()
    {
        $this->usersProvider = Providers::getUsersDataProvider();

        Events::registerEvent(EventList::PLAYER_COMMAND_PREPROCESS_EVENT, [$this, "handleMessage"]);
    }

    public function getAttributes() : array
    {
        return [
        ];
    }

    public function handleMessage(PlayerCommandPreprocessEvent $event)
    {
        $sender = FlatParkPlayer::cast($event->getPlayer());

        if(!$sender->isAuthorized()) {
            return;
        }

        $message = $event->getMessage();

        if($message[0] === ChatConstants::COMMAND_PREFIX) {
            $this->usersProvider->saveExecutedCommand($sender->getName(), substr($message, 1));
        } else {
            $this->usersProvider->saveChatMessage($sender->getName(), $message);
        }
    }
}
?>