<?php
namespace flatpark;

use Exception;
use flatpark\components\administrative\BanSystem;
use flatpark\components\Auth;
use flatpark\components\BossBar;
use flatpark\components\map\ATM;
use flatpark\components\FastFood;
use flatpark\components\chat\Chat;
use flatpark\components\StatusBar;
use flatpark\components\phone\Phone;
use flatpark\components\Broadcasting;
use flatpark\components\map\ClearLagg;
use flatpark\components\base\Component;
use flatpark\components\chat\ChatAudit;
use flatpark\components\map\Navigation;
use flatpark\components\WorldProtector;
use flatpark\components\map\FloatingTexts;
use flatpark\components\map\TrafficLights;
use flatpark\components\vehicles\Vehicles;
use flatpark\defaults\ComponentAttributes;
use flatpark\components\map\PlayersLocation;
use flatpark\components\organisations\PayDay;
use flatpark\components\administrative\Reports;
use flatpark\components\settings\WorldSettings;
use flatpark\components\administrative\Tracking;
use flatpark\components\settings\EntitySettings;
use flatpark\components\settings\PlayerSettings;
use flatpark\components\organisations\Organisations;
use flatpark\components\administrative\PermissionsSwitch;

class Components
{
    private static array $components;

    public static function initializeAll()
    {
        self::$components = [
            new EntitySettings,
            new PlayerSettings,
            new WorldSettings,
            new Chat,
            new ChatAudit,
            new Organisations,
            new Phone,
            new Auth,
            new BossBar,
            new Broadcasting,
            new FastFood,
            new Navigation,
            new PlayersLocation,
            new PayDay,
            new Reports,
            new StatusBar,
            new Tracking,
            new Vehicles,
            new TrafficLights,
            new WorldProtector,
            new PermissionsSwitch,
            new FloatingTexts,
            new ATM,
            new ClearLagg,
            new BanSystem
        ];

        foreach(self::$components as $component) {
            $component->initialize();
        }
    }

    public static function getComponent(string $componentName) : Component
    {
        foreach (self::$components as $component) {
            if($componentName === $component::class) {
                if(!$component->hasAttribute(ComponentAttributes::SHARED)) {
                    throw new Exception("Component is not shareable");
                }

                return $component;
            }
        }

        throw new Exception("Component '" . $componentName . "' does not exist");
    }
}