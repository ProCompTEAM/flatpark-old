<?php
namespace flatpark\providers\data;

use flatpark\models\dtos\FloatingTextDto;
use flatpark\models\dtos\LocalFloatingTextDto;
use flatpark\models\dtos\PositionDto;
use flatpark\providers\base\DataProvider;

class FloatingTextsDataProvider extends DataProvider
{
    public const ROUTE = "floating-texts";

    public function getRoute(): string
    {
        return self::ROUTE;
    }

    public function getAll() : array
    {
        $data = $this->createRequest("get-all", null);

        return $this->createArrayDto($data);
    }

    public function save(LocalFloatingTextDto $dto) : FloatingTextDto
    {
        return $this->createDto($this->createRequest("save", $dto));
    }

    public function remove(PositionDto $dto) : bool
    {
        return (bool) $this->createRequest("remove", $dto);
    }

    protected function createDto(array $data) : FloatingTextDto
    {
        $dto = new FloatingTextDto;
        $dto->set($data);
        return $dto;
    }

    private function createArrayDto(array $data)
    {
        $dtos = [];

        foreach($data as $rawDto) {
            array_push($dtos, $this->createDto($rawDto));
        }

        return $dtos;
    }
}