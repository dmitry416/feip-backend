<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'vessels')]
class Vessel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 20, unique: true)]
    #[Assert\NotBlank]
    private ?string $imo = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 2)]
    #[Assert\NotBlank]
    private ?string $flag = null;

    public function getId(): ?int { return $this->id; }

    public function getImo(): ?string { return $this->imo; }
    public function setImo(string $imo): self { $this->imo = $imo; return $this; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getFlag(): ?string { return $this->flag; }
    public function setFlag(string $flag): self { $this->flag = $flag; return $this; }
}
