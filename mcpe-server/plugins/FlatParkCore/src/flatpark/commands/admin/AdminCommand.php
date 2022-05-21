<?php
namespace flatpark\commands\admin;

use flatpark\Providers;

use flatpark\Components;
use pocketmine\event\Event;

use flatpark\defaults\Sounds;
use flatpark\components\phone\Phone;
use flatpark\defaults\Defaults;
use flatpark\components\administrative\Tracking;
use flatpark\utils\ArraysUtility;
use flatpark\defaults\Permissions;
use flatpark\commands\base\Command;
use flatpark\providers\ProfileProvider;
use flatpark\common\player\FlatParkPlayer;
use flatpark\components\organisations\Organisations;

class AdminCommand extends Command
{
    public const CURRENT_COMMAND = "a";

    private Phone $phone;

    private Organisations $organisations;

    private Tracking $tracking;

    private ProfileProvider $profileProvider;

    public function __construct()
    {
        $this->phone = Components::getComponent(Phone::class);
        $this->organisations = Components::getComponent(Organisations::class);
        $this->tracking = Components::getComponent(Tracking::class);
        $this->profileProvider = Providers::getProfileProvider();
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
            Permissions::OPERATOR,
            Permissions::ADMINISTRATOR
        ];
    }

    public function execute(FlatParkPlayer $player, array $args = array(), Event $event = null)
    {
        if (self::argumentsNo($args)) {
            $player->sendMessage("AdminCmdNoArg");
            return;
        }
        
        $command = strtolower($args[0]);

        switch($command) {
            case 'msg':
            case 'sms':
                $this->commandMessage($args);
            break;

            case 'setorg':
                $this->commandSetOrg($player, $args);
            break;

            case 'near':
                $this->commandNear($player);
            break;

            case 'arest':
                $this->commandArest($player, $args);
            break;

            case 'tags':
                $this->commandTags($player, $args);
            break;

            case 'addtag':
                $this->commandAddTag($player, $args);
            break;

            case 'remtag':
                $this->commandRemoveTag($player, $args);
            break;

            case 'hide':
                $this->commandHide($player);
            break;

            case 'show':
                $this->commandShow($player);
            break;
            
            case 'track':
                $this->commandTrack($player, $args);
            break;

            case 'untrack':
                $this->commandUnTrack($player, $args);
            break;

            case 'siren':
                $this->commandSiren();
            break;
        }
    }

    public function commandMessage(array $args)
    {
        $text = ArraysUtility::getStringFromArray($args, 1);

        foreach($this->getServer()->getOnlinePlayers() as $player) {
            $player = FlatParkPlayer::cast($player);
            $player->sendLocalizedMessage("{PhoneSend}" . Defaults::CONTEXT_NAME);
            $player->sendMessage("§b[➪] " . $text);
        }
    }

    public function commandSetOrg(FlatParkPlayer $player, array $args)
    {
        if(!$this->countArguments($player, $args, 2)) {
            return;
        }

        $oid = $args[2];
        $targetPlayer = FlatParkPlayer::cast($this->getServer()->getPlayerByPrefix($args[1]));
        
        if($targetPlayer === null) {
            return;
        }

        $targetPlayer->getSettings()->organisation = $oid; 
        $this->profileProvider->saveSettings($targetPlayer);

        $player->sendMessage("AdminCmdSetOrg1");
        $targetPlayer->sendLocalizedMessage("{GroupYou}". $this->organisations->getName($oid));
    }

    public function commandNear(FlatParkPlayer $player)
    {
        $rad = 7;
        
        $list = $this->getCore()->getRegionPlayers($player->getPosition(), $rad);

        $f = "AdminCmdPlayerNear";
        foreach($list as $p) {
            $f .= " " . $p->getName();
        }
        $player->sendMessage($f);
    }

    public function commandArest(FlatParkPlayer $player, array $args)
    {
        if(!$this->countArguments($player, $args, 1)) {
            return;
        }

        $targetPlayer = $this->getServer()->getPlayerByPrefix($args[1]);

        if($targetPlayer === null) {
            $player->sendMessage("§cИгрок §e" . $args[1] . "§c не на сервере.");
            return;
        }

        $targetPlayer = FlatParkPlayer::cast($targetPlayer);

        $targetPlayer->arest();

        $message = "{AdminCmdArestPart1}" . $player->getProfile()->fullName . "{AdminCmdArestPart2}" . $targetPlayer->getProfile()->fullName;

        foreach($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
            $onlinePlayer = FlatParkPlayer::cast($onlinePlayer);

            $onlinePlayer->sendLocalizedMessage($message);
        }
    }

    public function commandTags(FlatParkPlayer $player, array $args)
    {
        if(!$this->countArguments($player, $args, 1)) {
            return;
        }

        $targetPlayer = FlatParkPlayer::cast($this->getServer()->getPlayerByPrefix($args[1]));

        if($targetPlayer === null) {
            return;
        }

        $player->sendMessage($targetPlayer->getSettings()->attributes);
    }

    public function commandAddTag(FlatParkPlayer $player, array $args)
    {
        if(!$this->countArguments($player, $args, 2)) {
            return;
        }

        $targetPlayer = $this->getServer()->getPlayerByPrefix($args[1]);
        $targetPlayer = FlatParkPlayer::cast($targetPlayer);

        if($targetPlayer === null or !isset($args[2])) {
            return;
        }

        $targetPlayer->changeAttribute(strtoupper($args[2]));

        $player->sendMessage("AdminCmdSetTag");
    }

    public function commandRemoveTag(FlatParkPlayer $player, array $args)
    {
        if(!$this->countArguments($player, $args, 2)) {
            return;
        }

        $targetPlayer = $this->getServer()->getPlayerByPrefix($args[1]);
        $targetPlayer = FlatParkPlayer::cast($targetPlayer);
        
        if($targetPlayer === null or !isset($args[2])) {
            return;
        }

        $targetPlayer->changeAttribute(strtoupper($args[2]), false);

        $player->sendMessage("AdminCmdRemoveTag");
    }

    public function commandHide(FlatParkPlayer $player)
    {
        foreach($this->getServer()->getOnlinePlayers() as $p) {
            $p->hidePlayer($player);
        }
        
        $player->sendMessage("AdminCmdHide");
    }

    public function commandShow(FlatParkPlayer $player)
    {
        foreach($this->getServer()->getOnlinePlayers() as $p) {
            $p->showPlayer($player);
        }
        
        $player->sendMessage("AdminCmdShow");
    }

    public function commandTrack(FlatParkPlayer $player, array $args)
    {
        if (!$this->countArguments($player, $args, 1)) {
            return;
        }

        $target = $this->getServer()->getPlayerByPrefix($args[1]);

        if ($target === null) {
            $player->sendMessage("TrackerPlayerNotExists");
            return;
        }

        if ($this->tracking->isTracked($target)) {
            $player->sendMessage("TrackerAlreadyTracked");
            return;
        }

        $this->tracking->enableTrack($target, $player);
    }

    public function commandUnTrack(FlatParkPlayer $player, array $args)
    {
        if (!$this->countArguments($player, $args, 1)) {
            return;
        }

        $target = $this->getServer()->getPlayerByPrefix($args[1]);

        if ($target === null) {
            $player->sendMessage("TrackerPlayerNotExists");
            return;
        }

        if (!$this->tracking->isTracked($target)) {
            $player->sendMessage("TrackerAlreadyUnTracked");
            return;
        }

        $this->tracking->disableTrack($target, $player);
    }

    public function commandSiren()
    {
        foreach($this->getServer()->getOnlinePlayers() as $player) {
            $player = FlatParkPlayer::cast($player);
            $player->sendSound(Sounds::SIREN_SOUND);
        }
    }

    private function countArguments(FlatParkPlayer $player, array $args, int $minCount)
    {
        if(count($args) < $minCount + 1) {
            $player->sendMessage("NoArguments");
            return false;
        }

        return true;
    }
}