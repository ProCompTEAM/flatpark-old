<?php
namespace flatpark\components\settings;

use flatpark\Events;
use flatpark\Providers;
use flatpark\Components;
use pocketmine\item\ItemIds;
use pocketmine\utils\Config;
use pocketmine\entity\Entity;
use flatpark\utils\MathUtility;
use flatpark\defaults\EventList;
use flatpark\components\chat\Chat;
use flatpark\defaults\MapConstants;
use flatpark\components\base\Component;
use flatpark\defaults\PlayerAttributes;
use flatpark\common\player\FlatParkPlayer;
use flatpark\defaults\OrganisationConstants;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityDamageEvent;
use flatpark\components\organisations\Organisations;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class EntitySettings extends Component
{
    private const NON_REDUCEABLE_MAXIMAL_DAMAGE = 1;

    private Config $config;

    private array $reasons;

    private Chat $chat;

    public function initialize()
    {
        $this->chat = Components::getComponent(Chat::class);

        Events::registerEvent(EventList::ENTITY_DAMAGE_EVENT, [$this, "processEntityDamageEvent"]);

        $this->config = new Config($this->getCore()->getTargetDirectory() . "greenZones.json", Config::JSON); //TODO: INTO DataCenter
        $this->reasons = array("сотрясения мозга", "потери сознания", "ряда переломов");
    }

    public function getAttributes() : array
    {
        return [
        ];
    }

    public function processEntityDamageEvent(EntityDamageEvent $event)
    {
        if(!$event->getEntity() instanceof FlatParkPlayer) {
            return;
        }

        $damager = null;

        if($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();

            $this->tryToReduceDamage($event);

            $this->checkForStunning($event);
        }

        if($event->isCancelled()) {
            return;
        }

        if($this->checkForPlayerKilling($event->getFinalDamage(), $event->getEntity(), $damager)) {
            $event->cancel();
        }
    }

    private function tryToReduceDamage(EntityDamageByEntityEvent $event)
    {
        if($event->getBaseDamage() <= self::NON_REDUCEABLE_MAXIMAL_DAMAGE) {
            return;
        }

        $event->setBaseDamage($event->getBaseDamage() / 2);
    }

    private function checkForStunning(EntityDamageByEntityEvent $event)
    {
        if(!$event->getDamager() instanceof FlatParkPlayer or !$event->getEntity() instanceof FlatParkPlayer) {
            return;
        }

        $damager = FlatParkPlayer::cast($event->getDamager());
        $player = FlatParkPlayer::cast($event->getEntity());

        if($damager->getSettings()->organisation === OrganisationConstants::SECURITY_WORK and $damager->getInventory()->getItemInHand()->getId() === ItemIds::STICK) {
            $this->processStunAction($player, $damager);
        }

        if(!$this->canPlayerHurt($player, $damager)) {
            $event->cancel();
        }
    }

    private function checkForPlayerKilling(int $finalDamage, FlatParkPlayer $victim, ?Entity $damager) : bool
    {
        if($victim->getHealth() > $finalDamage) {
            return false;
        }

        Providers::getMapProvider()->teleportPoint($victim, MapConstants::POINT_NAME_HOSPITAL);
        $effects = [
            "slowness",
            "weakness",
            "poison"
        ];
        $effectManager = $victim->getEffects();
        foreach($effects as $effectName) {
            $effect = VanillaEffects::fromString($effectName);
            $instance = new EffectInstance($effect, 5000, 1, true);
            $effectManager->add($instance);
        }
        $victim->setHealth(4);

        $victim->sendLocalizedMessage("{WakeUp1}". $this->getRandomDeathReason() . ".");
        $victim->sendMessage("DamageSearchDoctor");
        $victim->sendMessage("DamageCallPolice");

        $victim->sleepOn($victim->getPosition());

        $this->broadcastPlayerDeath($victim, $damager);

        return true;
    }

    private function processStunAction(FlatParkPlayer $victim, FlatParkPlayer $policeMan)
    {
        $this->chat->sendLocalMessage($policeMan, "{StunGunInArm}", "§d : ", 10);
        $this->chat->sendLocalMessage($victim, "{Stun}", "§d : ", 12);
        
        $victim->changeAttribute(PlayerAttributes::WANTED);

        $victim->setImmobile(true);
        $victim->getStatesMap()->bar = "§6ВЫ ОГЛУШЕНЫ!";
    }

    private function broadcastPlayerDeath(FlatParkPlayer $victim, ?Entity $damager)
    {
        if (isset($damager) and $damager instanceof FlatParkPlayer) {
            $message = "§7[§6!§7] PvP : §c" . $damager->getName() . " убил " . $victim->getName();
        } else {
            $message = "§7[§6!§7] Kill : §c"." игрок  " . $victim->getName()." умер..";
        }

        foreach($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
            $onlinePlayer = FlatParkPlayer::cast($onlinePlayer);

            if($onlinePlayer->isAdministrator()) {
                $onlinePlayer->sendMessage($message);
            }
        }
    }

    //TODO: Move it to DataCenter
    private function canPlayerHurt(FlatParkPlayer $player, FlatParkPlayer $damager) : bool
    {
        if($damager->getStatesMap()->damageDisabled) {
            $damager->sendMessage("NoPVP");
            return false;
        }
        
        foreach($this->config->getAll() as $name) {
            $x1 = $this->config->getNested("$name.pos1.x");
            $y1 = $this->config->getNested("$name.pos1.y");
            $z1 = $this->config->getNested("$name.pos1.z");
            $x2 = $this->config->getNested("$name.pos2.x");
            $y2 = $this->config->getNested("$name.pos2.y");
            $z2 = $this->config->getNested("$name.pos2.z");

            $x = floor($player->getLocation()->getX());
            $y = floor($player->getLocation()->getY());
            $z = floor($player->getLocation()->getZ());

            if(MathUtility::interval($x ,$x1, $x2) 
                and MathUtility::interval($y, $y1, $y2) 
                    and MathUtility::interval($z, $z1 , $z2)) {
                $damager->sendMessage("SafeArea");
                return false;
            }
        }

        return true;
    }

    private function getRandomDeathReason() : string
    {
        return $this->reasons[mt_rand(0, count($this->reasons) - 1)];
    }
}