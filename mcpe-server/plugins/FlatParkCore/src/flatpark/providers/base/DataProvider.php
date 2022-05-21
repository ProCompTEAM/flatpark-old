<?php
namespace flatpark\providers\base;

abstract class DataProvider extends Provider
{
    public abstract function getRoute() : string;

    protected function createDto(array $data) {}

    public function createRequest(string $remoteMethod, $data = [])
    {
        return $this->getCore()->getDataCenter()->createRequest($this->getRoute(), $remoteMethod, $data);
    }
}