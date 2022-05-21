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
use flatpark\commands\CasinoCommand;
use flatpark\commands\DonateCommand;
use flatpark\commands\OnlineCommand;
use flatpark\defaults\ChatConstants;
use flatpark\commands\map\ATMCommand;
use flatpark\commands\map\GPSCommand;
use flatpark\commands\JailExitCommand;
use flatpark\commands\PassportCommand;
use flatpark\commands\AnimationCommand;
use flatpark\commands\GetSellerCommand;
use flatpark\commands\phone\SmsCommand;
use flatpark\commands\TransportCommand;
use flatpark\commands\phone\CallCommand;
use flatpark\commands\admin\AdminCommand;
use flatpark\commands\economy\PayCommand;
use flatpark\commands\map\GPSNearCommand;
use flatpark\commands\map\ToPointCommand;
use flatpark\commands\roleplay\DoCommand;
use flatpark\commands\roleplay\MeCommand;
use flatpark\commands\economy\BankCommand;
use flatpark\commands\map\AddPointCommand;
use flatpark\commands\report\CloseCommand;
use flatpark\commands\report\ReplyCommand;
use flatpark\commands\roleplay\TryCommand;
use flatpark\common\player\FlatParkPlayer;
use flatpark\commands\economy\MoneyCommand;
use flatpark\commands\report\ReportCommand;
use flatpark\commands\ResetPasswordCommand;
use flatpark\commands\roleplay\ShoutCommand;
use flatpark\commands\workers\PutBoxCommand;
use flatpark\commands\GetOrganisationCommand;
use flatpark\commands\map\RemovePointCommand;
use flatpark\commands\map\ToNearPointCommand;
use flatpark\commands\workers\GetFarmCommand;
use flatpark\commands\workers\PutFarmCommand;
use flatpark\commands\workers\TakeBoxCommand;
use flatpark\commands\roleplay\WhisperCommand;
use flatpark\commands\map\FloatingTextsCommand;
use flatpark\commands\organisations\AddCommand;
use flatpark\commands\base\OrganisationsCommand;
use flatpark\commands\economy\MoneyGiftCommand;
use flatpark\commands\organisations\HealCommand;
use flatpark\commands\organisations\InfoCommand;
use flatpark\commands\organisations\SellCommand;
use flatpark\commands\organisations\ShowCommand;
use flatpark\commands\permissions\SwitchCommand;
use flatpark\commands\organisations\ArestCommand;
use flatpark\commands\organisations\RadioCommand;
use flatpark\commands\organisations\NoFireCommand;
use flatpark\commands\organisations\RemoveCommand;
use flatpark\commands\organisations\GiveLicCommand;
use flatpark\commands\organisations\ChangeNameCommand;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

class Commands
{
    private $commands;
    private $organisationsCommands;

    public function __construct()
    {
        $this->initializeCommands();
        $this->initializeOrganisationsCommands();

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
            new CallCommand,
            new SmsCommand,
            new DoCommand,
            new MeCommand,
            new ShoutCommand,
            new TryCommand,
            new WhisperCommand,
            new GetFarmCommand,
            new PutBoxCommand,
            new PutFarmCommand,
            new TakeBoxCommand,
            new AnimationCommand,
            new CasinoCommand,
            new DonateCommand,
            new GetOrganisationCommand,
            new GetSellerCommand,
            new JailExitCommand,
            new LevelCommand,
            new MoneyCommand,
            new OnlineCommand,
            new PassportCommand,
            new PayCommand,
            new ResetPasswordCommand,
            new ReportCommand,
            new ReplyCommand,
            new CloseCommand,
            new BankCommand,
            new MoneyGiftCommand,
            new DayCommand,
            new NightCommand,
            new TransportCommand,
            new SwitchCommand,
            new FloatingTextsCommand,
            new ATMCommand,
            new BanCommand,
            new UnbanCommand
        ];
    }

    private function initializeOrganisationsCommands()
    {
        $this->organisationsCommands = [
            new AddCommand,
            new ArestCommand,
            new ChangeNameCommand,
            new GiveLicCommand,
            new HealCommand,
            new InfoCommand,
            new NoFireCommand,
            new RadioCommand,
            new RemoveCommand,
            new SellCommand,
            new ShowCommand
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

        if($arguments[0] === ChatConstants::ORGANISATIONS_COMMANDS_PREFIX) {
            return $this->executeOrganisationsCommand($player, array_slice($arguments, 1), $event);
        }

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

    private function executeOrganisationsCommand(FlatParkPlayer $player, array $arguments, PlayerCommandPreprocessEvent $event)
    {
        if(!isset($arguments[0])) {
            return;
        }

        $command = $this->getOrganisationsCommand($arguments[0]);

        if(!isset($command)) {
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

    private function getOrganisationsCommand(string $commandName) : ?OrganisationsCommand
    {
        foreach($this->organisationsCommands as $command) {
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