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

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTimestamp(): ?\DateTime
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTime $timestamp): static
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getIdUsers(): ?int
    {
        return $this->idUsers;
    }

    public function setIdUsers(?int $idUsers): static
    {
        $this->idUsers = $idUsers;

        return $this;
    }

    public function getIdActionTriggerTypes(): ?int
    {
        return $this->idActionTriggerTypes;
    }

    public function setIdActionTriggerTypes(?int $idActionTriggerTypes): static
    {
        $this->idActionTriggerTypes = $idActionTriggerTypes;

        return $this;
    }
}
// ENTITY RULE
