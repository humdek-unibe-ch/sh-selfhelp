<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users_groups')]
class UsersGroup
{
    #[ORM\Id]
    #[ORM\Column(name: 'id_users', type: 'integer')]
    private int $idUsers;

    #[ORM\Id]
    #[ORM\Column(name: 'id_groups', type: 'integer')]
    private int $idGroups;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'usersGroups')]
    #[ORM\JoinColumn(name: 'id_users', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Group::class, inversedBy: 'usersGroups')]
    #[ORM\JoinColumn(name: 'id_groups', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Group $group = null;

    public function getIdUsers(): ?int
    {
        return $this->idUsers;
    }
    public function setIdUsers(int $idUsers): self { $this->idUsers = $idUsers; return $this; }

    public function getIdGroups(): ?int
    {
        return $this->idGroups;
    }
    public function setIdGroups(int $idGroups): self { $this->idGroups = $idGroups; return $this; }

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
