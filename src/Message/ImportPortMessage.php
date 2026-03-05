<?php

namespace App\Message;

class ImportPortMessage
{
    private array $portData;

    public function __construct(array $portData)
    {
        $this->portData = $portData;
    }

    public function getPortData(): array
    {
        return $this->portData;
    }
}
