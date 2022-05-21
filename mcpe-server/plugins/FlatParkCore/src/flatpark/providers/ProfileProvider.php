<?php
namespace flatpark\providers;

use flatpark\common\player\FlatParkPlayer;
use flatpark\Providers;
use flatpark\providers\base\Provider;
use flatpark\providers\data\UsersDataProvider;

class ProfileProvider extends Provider
{
    private UsersDataProvider $usersDataProvider;

    public function __construct()
    {
        $this->usersDataProvider = Providers::getUsersDataProvider();
    }

    public function isNewPlayer(FlatParkPlayer $player)
    {
        return !$this->usersDataProvider->isUserExist($player->getName());
    }

    public function initializeProfile(FlatParkPlayer $player)
    {
        if($player->getStatesMap()->isNew) {
            $createdUserProfile = $this->usersDataProvider->createUserInternal($player->getName());
            $player->setProfile($createdUserProfile);
        } else {
            $this->loadProfile($player);
        }

        $this->loadSettings($player);
    }
    
    public function loadProfile(FlatParkPlayer $player)
    {
        $profile = $this->usersDataProvider->getUser($player->getName());
        $player->setProfile($profile);
    }

    public function loadSettings(FlatParkPlayer $player)
    {
        $settings = $this->usersDataProvider->getUserSettings($player->getName());
        $player->setSettings($settings);
    }
    
    public function saveProfile(FlatParkPlayer $player)
    {
        $this->usersDataProvider->updateUserData($player->getProfile());
    }

    public function saveSettings(FlatParkPlayer $player)
    {
        $this->usersDataProvider->updateUserSettings($player->getSettings());
    }
}