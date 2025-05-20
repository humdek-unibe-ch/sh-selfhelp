<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'validation_codes')]
class ValidationCode
{

    #[ORM\Id]
    #[ORM\Column(name: 'code', type: 'string', length: 16)]
    private string $code;

    #[ORM\Column(name: 'id_users', type: 'integer', nullable: true)]
    private ?int $idUsers = null;

    #[ORM\Column(name: 'created', type: 'datetime')]
    private \DateTimeInterface $created;

    #[ORM\Column(name: 'consumed', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $consumed = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'validationCodes', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'id_users', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Group::class, inversedBy: 'validationCodes', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'id_groups', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Group $group = null;

    public function getCode(): ?string
    {
        return $this->code;
    }
    public function setCode(string $code): self { $this->code = $code; return $this; }

    public function getIdUsers(): ?int
    {
        return $this->idUsers;
    }

    public function setIdUsers(?int $idUsers): static
    {
        $this->idUsers = $idUsers;

        return $this;
    }

    public function getCreated(): ?\DateTime
    {
        return $this->created;
    }

    public function setCreated(\DateTime $created): static
    {
        $this->created = $created;

        return $this;
    }

    public function getConsumed(): ?\DateTime
    {
        return $this->consumed;
    }

    public function setConsumed(?\DateTime $consumed): static
    {
        $this->consumed = $consumed;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function setGroup(?Group $group): static
    {
        $this->group = $group;
        return $this;
    }
}
// ENTITY RULE
