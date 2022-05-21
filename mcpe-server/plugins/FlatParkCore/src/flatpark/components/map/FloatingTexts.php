<?php
namespace flatpark\components\map;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use LogicException;
use flatpark\common\player\FlatParkPlayer;
use flatpark\components\base\Component;
use flatpark\defaults\ComponentAttributes;
use flatpark\defaults\EventList;
use flatpark\Events;
use flatpark\models\dtos\FloatingTextDto;
use flatpark\models\dtos\LocalFloatingTextDto;
use flatpark\models\dtos\PositionDto;
use flatpark\Providers;
use flatpark\providers\data\FloatingTextsDataProvider;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\world\Position;
use pocketmine\world\World;

class FloatingTexts extends Component
{
    private const FLOATING_TEXT_TAG = "FLOATING_TEXT";

    private const CREATE_CHOICE = 0;

    private const REMOVE_CHOICE = 1;

    private FloatingTextsDataProvider $floatingTextsDataProvider;

    private array $floatingTexts;

    public function initialize()
    {
        $this->floatingTextsDataProvider = Providers::getFloatingTextsDataProvider();

        $this->floatingTexts = $this->floatingTextsDataProvider->getAll();

        Events::registerEvent(EventList::PLAYER_JOIN_EVENT, [$this, "playerJoinEvent"]);
    }

    public function getAttributes() : array
    {
        return [
            ComponentAttributes::SHARED
        ];
    }

    public function playerJoinEvent(PlayerJoinEvent $event)
    {
        $this->initializeAllFloatingTexts($event->getPlayer());
    }

    public function getFloatingTexts()
    {
        return $this->floatingTexts;
    }

    public function getFloatingText(string $world, float $x, float $y, float $z) : ?FloatingTextDto
    {
        $x = floor($x);
        $y = floor($y);
        $z = floor($z);

        foreach($this->getFloatingTexts() as $floatingText) {
            $textX = floor($floatingText->x);
            $textY = floor($floatingText->y);
            $textZ = floor($floatingText->z);

            if($textX === $x and $textY === $y and $textZ === $z and $floatingText->world === $world) {
                return $floatingText;
            }
        }

        return null;
    }

    public function save(string $text, Position $position)
    {
        $worldName = $position->getWorld()->getDisplayName();

        $floatingText = $this->getFloatingText($worldName, $position->getX(), $position->getY(), $position->getZ());

        if(is_null($floatingText)) {
            $dto = $this->createLocalFloatingTextDto($text, $position);
        } else {
            $dto = $this->getLocalFloatingTextDto($floatingText);
            $dto->text = $text;

            $this->removeFromMemory($floatingText);
        }

        $floatingText = $this->floatingTextsDataProvider->save($dto);

        $this->addToMemory($floatingText);

        $this->showFloatingText($floatingText);
    }

    public function remove(Position $position) : bool
    {
        $worldName = $position->getWorld()->getDisplayName();

        $floatingText = $this->getFloatingText($worldName, $position->getX(), $position->getY(), $position->getZ());

        if(is_null($floatingText)) {
            return false;
        }

        $dto = $this->getPositionDto($floatingText->world, $floatingText->x, $floatingText->y, $floatingText->z);

        $this->floatingTextsDataProvider->remove($dto);

        $this->hideFloatingText($floatingText);
        $this->removeFromMemory($floatingText);

        return true;
    }

    public function initializeMenu(FlatParkPlayer $player)
    {
        $form = new SimpleForm([$this, "answerMenu"]);
        $form->setTitle("§bУправление текстами");
        $form->addButton("§eДобавить летающий текст");
        $form->addButton("§eУдалить летающий текст");
        $player->sendForm($form);
    }

    public function answerMenu(FlatParkPlayer $player, ?int $data = null)
    {
        if(!isset($data)) {
            $player->sendMessage("GoodLuck");
            return;
        }

        switch($data) {
            case self::CREATE_CHOICE:
                $this->initializeCreateForm($player);
            break;

            case self::REMOVE_CHOICE:
                $this->tryToRemove($player);
            break;

            default:
                throw new LogicException("Form answer out of choices");
        }
    }

    private function initializeCreateForm(FlatParkPlayer $player)
    {
        $form = new CustomForm([$this, "answerCreateForm"]);
        $form->setTitle("§bСоздать текст");
        $form->addInput("§eВпишите, пожалуйста, содержимое текста.");
        $player->sendForm($form);
    }

    public function answerCreateForm(FlatParkPlayer $player, ?array $data = null)
    {
        if(!isset($data)) {
            $player->sendMessage("GoodLuck");
            return;
        }

        $input = $data[0];

        if(strlen($input) === 0) {
            $player->sendMessage("LessSymbols");
            return;
        }

        $this->save($input, $player->getPosition());

        $player->sendMessage("FloatingTextsCreateSucces");
    }

    private function tryToRemove(FlatParkPlayer $player)
    {
        $removedStatus = $this->remove($player->getPosition());

        if(!$removedStatus) {
            $player->sendMessage("FloatingTextsDeleteUnSucces");
            return;
        }

        $player->sendMessage("FloatingTextsDeleteSucces");
    }

    private function getLocalFloatingTextDto(FloatingTextDto $floatingText) : LocalFloatingTextDto
    {
        $dto = new LocalFloatingTextDto;
        $dto->world = $floatingText->world;
        $dto->text = $floatingText->text;
        $dto->x = $floatingText->x;
        $dto->y = $floatingText->y;
        $dto->z = $floatingText->z;
        return $dto;
    }

    private function getPositionDto(string $world, float $x, float $y, float $z) : PositionDto
    {
        $dto = new PositionDto;
        $dto->world = $world;
        $dto->x = $x;
        $dto->y = $y;
        $dto->z = $z;
        return $dto;
    }

    private function createLocalFloatingTextDto(string $text, Position $position) : LocalFloatingTextDto
    {
        $dto = new LocalFloatingTextDto;
        $dto->text = $text;
        $dto->world = $position->getWorld()->getDisplayName();
        $dto->x = $position->getX();
        $dto->y = $position->getY();
        $dto->z = $position->getZ();
        return $dto;
    }

    private function addToMemory(FloatingTextDto $dto)
    {
        array_push($this->floatingTexts, $dto);
    }

    private function removeFromMemory(FloatingTextDto $dto)
    {
        $floatingTextIndex = array_search($dto, $this->floatingTexts);

        if(!isset($this->floatingTexts[$floatingTextIndex])) {
            return null;
        }

        unset($this->floatingTexts[$floatingTextIndex]);
    }

    private function initializeAllFloatingTexts(FlatParkPlayer $player)
    {
        foreach($this->getFloatingTexts() as $floatingText) {
            $world = $this->getServer()->getWorldManager()->getWorldByName($floatingText->world);

            if(!isset($world)) {
                return;
            }

            $position = new Position($floatingText->x, $floatingText->y + 0.5, $floatingText->z, $world);

            $player->setFloatingText($position, $floatingText->text, self::FLOATING_TEXT_TAG);
        }

        $player->showFloatingTexts();
    }

    private function showFloatingText(FloatingTextDto $floatingText)
    {
        $world = $this->getServer()->getWorldManager()->getWorldByName($floatingText->world);

        if(!isset($world)) {
            return;
        }

        $position = new Position($floatingText->x, $floatingText->y + 0.5, $floatingText->z, $world);

        foreach($this->getServer()->getOnlinePlayers() as $player) {
            $this->showFloatingTextForPlayer($player, $floatingText->text, $world, $position);
        }
    }

    private function showFloatingTextForPlayer(FlatParkPlayer $player, string $text, World $world, Position $position)
    {
        if($world !== $player->getWorld()) {
            return;
        }

        $floatingText = $player->getFloatingText($position);

        if(!isset($floatingText)) {
            $player->setFloatingText($position, $text, self::FLOATING_TEXT_TAG);
        } else {
            $floatingText->text = $text;

            $player->updateFloatingText($floatingText);
        }

        $player->showFloatingTexts();
    }

    private function hideFloatingText(FloatingTextDto $dto)
    {
        $world = $this->getServer()->getWorldManager()->getWorldByName($dto->world);

        if(!isset($world)) {
            return;
        }

        $position = new Position($dto->x, $dto->y + 0.5, $dto->z, $world);

        foreach($this->getServer()->getOnlinePlayers() as $player) {
            $this->hideFloatingTextForPlayer($player, $position);
        }
    }

    private function hideFloatingTextForPlayer(FlatParkPlayer $player, Position $position)
    {
        $player->unsetFloatingText($player->getFloatingText($position));

        $player->showFloatingTexts();
    }
}