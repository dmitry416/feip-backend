<?php

namespace App\DTO;

use Symfony\Component\Serializer\Annotation\SerializedName;

class ImportDataDTO
{
    private array $vessels = [];
    private array $ports = [];
    private array $companies = [];

    public function getVessels(): array
    {
        return $this->vessels;
    }

    #[SerializedName('vessels')]
    public function setVessels(array $vessels): self
    {
        $this->vessels = $vessels;
        return $this;
    }

    public function getPorts(): array
    {
        return $this->ports;
    }

    #[SerializedName('ports')]
    public function setPorts(array $ports): self
    {
        $this->ports = $ports;
        return $this;
    }

    public function getCompanies(): array
    {
        return $this->companies;
    }

    #[SerializedName('companies')]
    public function setCompanies(array $companies): self
    {
        $this->companies = $companies;
        return $this;
    }
}
