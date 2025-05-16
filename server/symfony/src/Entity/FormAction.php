<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'formActions')]
class FormAction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: 'string', length: 200)]
    private string $name;

    #[ORM\Column(name: 'id_formProjectActionTriggerTypes', type: 'integer')]
    private int $idFormProjectActionTriggerTypes;

    #[ORM\Column(name: 'config', type: 'text', nullable: true)]
    private ?string $config = null;

    #[ORM\Column(name: 'id_dataTables', type: 'integer', nullable: true)]
    private ?int $idDataTables = null;

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getIdFormProjectActionTriggerTypes(): int { return $this->idFormProjectActionTriggerTypes; }
    public function setIdFormProjectActionTriggerTypes(int $id): self { $this->idFormProjectActionTriggerTypes = $id; return $this; }
    public function getConfig(): ?string { return $this->config; }
    public function setConfig(?string $config): self { $this->config = $config; return $this; }
    public function getIdDataTables(): ?int { return $this->idDataTables; }
    public function setIdDataTables(?int $idDataTables): self { $this->idDataTables = $idDataTables; return $this; }
}
// ENTITY RULE
