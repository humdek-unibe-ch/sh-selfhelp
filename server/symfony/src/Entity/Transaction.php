<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'transactions')]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'transaction_time', type: 'datetime')]
    private \DateTimeInterface $transactionTime;

    #[ORM\Column(name: 'id_transactionTypes', type: 'integer', nullable: true)]
    private ?int $idTransactionTypes = null;

    #[ORM\Column(name: 'id_transactionBy', type: 'integer', nullable: true)]
    private ?int $idTransactionBy = null;

    #[ORM\Column(name: 'id_users', type: 'integer', nullable: true)]
    private ?int $idUsers = null;

    #[ORM\Column(name: 'table_name', type: 'string', length: 100, nullable: true)]
    private ?string $tableName = null;

    #[ORM\Column(name: 'id_table_name', type: 'integer', nullable: true)]
    private ?int $idTableName = null;

    #[ORM\Column(name: 'transaction_log', type: 'text', nullable: true)]
    private ?string $transactionLog = null;

    #[ORM\ManyToOne(targetEntity: Lookup::class)]
    #[ORM\JoinColumn(name: 'id_transactionTypes', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Lookup $transactionType = null;

    #[ORM\ManyToOne(targetEntity: Lookup::class)]
    #[ORM\JoinColumn(name: 'id_transactionBy', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Lookup $transactionBy = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_users', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?User $user = null;

    public function getId(): ?int { return $this->id; }
    public function getTransactionTime(): \DateTimeInterface { return $this->transactionTime; }
    public function setTransactionTime(\DateTimeInterface $transactionTime): self { $this->transactionTime = $transactionTime; return $this; }
    public function getIdTransactionTypes(): ?int { return $this->idTransactionTypes; }
    public function setIdTransactionTypes(?int $idTransactionTypes): self { $this->idTransactionTypes = $idTransactionTypes; return $this; }
    public function getIdTransactionBy(): ?int { return $this->idTransactionBy; }
    public function setIdTransactionBy(?int $idTransactionBy): self { $this->idTransactionBy = $idTransactionBy; return $this; }
    public function getIdUsers(): ?int { return $this->idUsers; }
    public function setIdUsers(?int $idUsers): self { $this->idUsers = $idUsers; return $this; }
    public function getTableName(): ?string { return $this->tableName; }
    public function setTableName(?string $tableName): self { $this->tableName = $tableName; return $this; }
    public function getIdTableName(): ?int { return $this->idTableName; }
    public function setIdTableName(?int $idTableName): self { $this->idTableName = $idTableName; return $this; }
    public function getTransactionLog(): ?string { return $this->transactionLog; }
    public function setTransactionLog(?string $transactionLog): self { $this->transactionLog = $transactionLog; return $this; }
    public function getTransactionType(): ?Lookup { return $this->transactionType; }
    public function setTransactionType(?Lookup $transactionType): self { $this->transactionType = $transactionType; return $this; }
    public function getTransactionBy(): ?Lookup { return $this->transactionBy; }
    public function setTransactionBy(?Lookup $transactionBy): self { $this->transactionBy = $transactionBy; return $this; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
}
// ENTITY RULE
