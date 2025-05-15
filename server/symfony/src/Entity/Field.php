<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'fields')]
class Field
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: 'string', length: 100)]
    private string $name;

    #[ORM\Column(name: 'id_type', type: 'integer')]
    private int $idType;

    #[ORM\Column(name: 'display', type: 'boolean')]
    private bool $display = true;

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getIdType(): int { return $this->idType; }
    public function setIdType(int $idType): self { $this->idType = $idType; return $this; }
    public function isDisplay(): bool { return $this->display; }
    public function setDisplay(bool $display): self { $this->display = $display; return $this; }
}
// ENTITY RULE
