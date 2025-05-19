<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'dataCols')]
class DataCol
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(name: 'id_dataTables', type: 'integer', nullable: true)]
    private ?int $idDataTables = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getIdDataTables(): ?int
    {
        return $this->idDataTables;
    }

    public function setIdDataTables(?int $idDataTables): static
    {
        $this->idDataTables = $idDataTables;

        return $this;
    }
}
// ENTITY RULE
