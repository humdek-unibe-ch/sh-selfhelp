<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'actions')]
class Action
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: 'string', length: 200)]
    private string $name;

    #[ORM\ManyToOne(targetEntity: Lookup::class)]
    #[ORM\JoinColumn(name: 'id_actionTriggerTypes', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Lookup $actionTriggerType = null; // ENTITY RULE

    #[ORM\Column(name: 'config', type: 'text', nullable: true)]
    private ?string $config = null;

    #[ORM\ManyToOne(targetEntity: DataTable::class)]
    #[ORM\JoinColumn(name: 'id_dataTables', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?DataTable $dataTable = null; // ENTITY RULE

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getActionTriggerType(): ?Lookup
    {
        return $this->actionTriggerType;
    }

    public function setActionTriggerType(?Lookup $actionTriggerType): static
    {
        $this->actionTriggerType = $actionTriggerType;
        return $this;
    }
    // ENTITY RULE

    public function getConfig(): ?string
    {
        return $this->config;
    }

    public function setConfig(?string $config): static
    {
        $this->config = $config;

        return $this;
    }

    public function getDataTable(): ?DataTable
    {
        return $this->dataTable;
    }

    public function setDataTable(?DataTable $dataTable): static
    {
        $this->dataTable = $dataTable;
        return $this;
    }
    // ENTITY RULE
}
// ENTITY RULE


