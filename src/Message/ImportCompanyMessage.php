<?php

namespace App\Message;

class ImportCompanyMessage
{
    private array $companyData;

    public function __construct(array $companyData)
    {
        $this->companyData = $companyData;
    }

    public function getCompanyData(): array
    {
        return $this->companyData;
    }
}
