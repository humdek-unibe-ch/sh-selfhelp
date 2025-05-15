<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'dataRows')]
class DataRow
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'id_dataTables', type: 'integer', nullable: true)]
    private ?int $idDataTables = null;

    #[ORM\Column(name: 'timestamp', type: 'datetime')]
    private \DateTimeInterface $timestamp;

    #[ORM\Column(name: 'id_users', type: 'integer', nullable: true)]
    private ?int $idUsers = null;

    #[ORM\Column(name: 'id_actionTriggerTypes', type: 'integer', nullable: true)]
    private ?int $idActionTriggerTypes = null;

    public function getId(): ?int { return $this->id; }
    public function getIdDataTables(): ?int { return $this->idDataTables; }
    public function setIdDataTables(?int $idDataTables): self { $this->idDataTables = $idDataTables; return $this; }
    public function getTimestamp(): \DateTimeInterface { return $this->timestamp; }
    public function setTimestamp(\DateTimeInterface $timestamp): self { $this->timestamp = $timestamp; return $this; }
    public function getIdUsers(): ?int { return $this->idUsers; }
    public function setIdUsers(?int $idUsers): self { $this->idUsers = $idUsers; return $this; }
    public function getIdActionTriggerTypes(): ?int { return $this->idActionTriggerTypes; }
    public function setIdActionTriggerTypes(?int $idActionTriggerTypes): self { $this->idActionTriggerTypes = $idActionTriggerTypes; return $this; }
}
// ENTITY RULE
