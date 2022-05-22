<?php
namespace flatpark;

use flatpark\commands\admin\BanCommand;
use flatpark\commands\admin\UnbanCommand;
use pocketmine\event\Event;
use flatpark\defaults\EventList;
use flatpark\commands\DayCommand;
use flatpark\defaults\Permissions;
use flatpark\commands\base\Command;
use flatpark\commands\LevelCommand;
use flatpark\commands\NightCommand;
use flatpark\commands\OnlineCommand;
use flatpark\defaults\ChatConstants;
use flatpark\commands\map\GPSCommand;
use flatpark\commands\PassportCommand;
use flatpark\commands\AnimationCommand;
use flatpark\commands\TransportCommand;
use flatpark\commands\admin\AdminCommand;
use flatpark\commands\map\GPSNearCommand;
use flatpark\commands\map\ToPointCommand;
use flatpark\commands\roleplay\DoCommand;
use flatpark\commands\roleplay\MeCommand;
use flatpark\commands\map\AddPointCommand;
use flatpark\commands\report\CloseCommand;
use flatpark\commands\report\ReplyCommand;
use flatpark\commands\roleplay\TryCommand;
use flatpark\common\player\FlatParkPlayer;
use flatpark\commands\report\ReportCommand;
use flatpark\commands\ResetPasswordCommand;
use flatpark\commands\roleplay\ShoutCommand;
use flatpark\commands\map\RemovePointCommand;
use flatpark\commands\map\ToNearPointCommand;
use flatpark\commands\roleplay\WhisperCommand;
use flatpark\commands\map\FloatingTextsCommand;
use flatpark\commands\base\OrganisationsCommand;
use flatpark\commands\permissions\SwitchCommand;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

class Commands
{
    private $commands;
    private $organisationsCommands;

    public function __construct()
    {
        $this->initializeCommands();

        Events::registerEvent(EventList::PLAYER_COMMAND_PREPROCESS_EVENT, [$this, "executeInputData"]);
    }

    public function getCommands() : array
    {
        return $this->commands;
    }

    public function getOrganisationsCommands() : array
    {
        return $this->organisationsCommands;
    }

    private function initializeCommands()
    {
        $this->commands = [
            new AdminCommand,
            new AddPointCommand,
            new GPSCommand,
            new GPSNearCommand,
            new RemovePointCommand,
            new ToNearPointCommand,
            new ToPointCommand,
            new DoCommand,
            new MeCommand,
            new ShoutCommand,
            new TryCommand,
            new WhisperCommand,
            new AnimationCommand,
            new LevelCommand,
            new OnlineCommand,
            new ResetPasswordCommand,
            new ReportCommand,
            new ReplyCommand,
            new CloseCommand,
            new DayCommand,
            new NightCommand,
            new TransportCommand,
            new SwitchCommand,
            new FloatingTextsCommand,
            new BanCommand,
            new UnbanCommand
        ];
    }

    public function executeInputData(PlayerCommandPreprocessEvent $event)
    {
        $player = FlatParkPlayer::cast($event->getPlayer());

        if(!$player->isAuthorized()) {
            return;
        }

        if($event->getMessage()[0] !== ChatConstants::COMMAND_PREFIX) {
            return;
        }

        $rawCommand = substr($event->getMessage(), 1);
        $arguments = explode(Command::ARGUMENTS_SEPERATOR, $rawCommand);

        $command = $this->getCommand($arguments[0]);

        if($command === null) {
            return;
        }

        $arguments = array_slice($arguments, 1);

        $event->cancel();

        if(!$this->checkPermissions($player, $command, $event)) {
            return;
        }

        $command->execute($player, $arguments, $event);
    }

    private function getCommand(string $commandName) : ?Command
    {
        foreach($this->commands as $command) {
            if(in_array($commandName, $command->getCommand())) {
                return $command;
            }
        }

        return null;
    }

    private function checkPermissions(FlatParkPlayer $player, Command $command, ?Event $event = null) : bool
    {
        if($this->hasPermissions($player, $command)) {
            return true;
        }

        $player->sendMessage("NoPermission1");
        $player->sendMessage("NoPermission2");

        return false;
    }

    private function hasPermissions(FlatParkPlayer $player, Command $command) : bool
    {
        $permissions = $command->getPermissions();

        if(in_array(Permissions::ANYBODY, $permissions)) {
            return true;
        }

        if(in_array(Permissions::OPERATOR, $permissions) or $player->isOperator()) {
            return true;
        }

        if($player->hasPermissions($permissions)) {
            return true;
        }

        return false;
    }
}