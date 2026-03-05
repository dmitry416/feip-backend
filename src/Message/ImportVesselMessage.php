<?php

namespace App\Message;

class ImportVesselMessage
{
    private array $vesselData;

    public function __construct(array $vesselData)
    {
        $this->vesselData = $vesselData;
    }

    public function getVesselData(): array
    {
        return $this->vesselData;
    }
}
