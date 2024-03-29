<?php
namespace flatpark\components\map;

use flatpark\Tasks;
use flatpark\defaults\TimeConstants;
use flatpark\components\base\Component;
use flatpark\defaults\ComponentAttributes;

class Navigation extends Component
{
    public $world;
    
    public function initialize()
    {
        Tasks::registerRepeatingAction(TimeConstants::NAVIGATION_ROUTES_UPDATE_INTERVAL, [$this, "updateRoutes"]);
        $this->world = $this->getServer()->getWorldManager()->getDefaultWorld();
    }

    public function getAttributes() : array
    {
        return [
            ComponentAttributes::STANDALONE
        ];
    }

    public function updateRoutes()
    {
        foreach($this->getServer()->getOnlinePlayers() as $player) {
            if($player->getStatesMap()->gps != null) {
                $x = round($player->getStatesMap()->gps->getX() - $player->getLocation()->getX());
                $y = round($player->getStatesMap()->gps->getZ() - $player->getLocation()->getZ());
                
                $label = "";
                
                if($x >= -12 and $x <= 12 and $y >= -12 and $y <= 12) {
                    $player->sendMessage("CommandGPSCome");
                    $player->sendMessage("CommandGPSNearUse");

                    $player->getStatesMap()->gps = null;
                    $player->getStatesMap()->bar = null;
                    return;
                }
                
                //head direction
                $yaw = $player->getLocation()->getYaw();
                
                if(($yaw <= 45 and $yaw >= 0) or ($yaw >= 315 and $yaw <= 359)) {
                    $x = -$x;
                    $y = -$y;
                }
                
                if(($yaw >= 45 and $yaw <= 135 )) {
                    $y = -$y;
                }

                if(($yaw >= 225 and $yaw <= 315)) {
                    $x = -$x;
                }
                
                //direction label
                while($x > -10000 and $y > -10000) {
                    if($x > -7 and $x < 7 and $y >= 0) { 
                        $label = "требуется разворот ↓"; 
                        break; 
                    } 
                    
                    if($x > -7 and $x < 7 and $y <  0) { 
                        $label = "двигайтесь прямо ↑";  
                        break; 
                    }
                    
                    if($y > -7 and $y < 7 and $x <  0) { 
                        $label = "наша цель слева ←";  
                        break; 
                    }
                    
                    if($y > -7 and $y < 7 and $x >= 0) { 
                        $label = "наша цель справа →"; 
                        break; 
                    }
                        
                    if($x > 0 and $y > 0) { 
                        $label = "юго-восточное направление ↘";
                        break; 
                    }
                    
                    if($x < 0 and $y > 0) { 
                        $label = "юго-западное направление ↙"; 
                        break; 
                    }
                    
                    if($x < 0 and $y < 0) { 
                        $label = "северо-западное направление ↖"; 
                        break; 
                    }
                    
                    if($x > 0 and $y < 0) { 
                        $label = "северо-восточное направление ↗"; 
                        break; 
                    }
                }
                
                $player->getStatesMap()->bar = "§7(§9Smart§6Navi§7) §8[" . $this->getL($player->getLocation()->getX(), $player->getLocation()->getZ(), $player->getStatesMap()->gps->getX(), $player->getStatesMap()->gps->getZ()) ."m] §a" . $label;
            }
        }	
    }
    
    public function getL($fromX, $fromY, $toX, $toY)
    {
        return round(sqrt(pow($toX - $fromX, 2) + pow($toY - $fromY, 2)));
    }
}