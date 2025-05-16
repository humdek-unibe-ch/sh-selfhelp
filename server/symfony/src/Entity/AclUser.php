<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'acl_users')]
class AclUser
{
    #[ORM\Id]
    #[ORM\Column(name: 'id_users', type: 'integer')]
    private int $idUsers;

    #[ORM\Id]
    #[ORM\Column(name: 'id_pages', type: 'integer')]
    private int $idPages;

    #[ORM\Column(name: 'acl_select', type: 'boolean', options: ['default' => 1])]
    private bool $aclSelect = true;

    #[ORM\Column(name: 'acl_insert', type: 'boolean', options: ['default' => 0])]
    private bool $aclInsert = false;

    #[ORM\Column(name: 'acl_update', type: 'boolean', options: ['default' => 0])]
    private bool $aclUpdate = false;

    #[ORM\Column(name: 'acl_delete', type: 'boolean', options: ['default' => 0])]
    private bool $aclDelete = false;

    // Getters and setters
    public function getIdUsers(): int { return $this->idUsers; }
    public function setIdUsers(int $idUsers): self { $this->idUsers = $idUsers; return $this; }

    public function getIdPages(): int { return $this->idPages; }
    public function setIdPages(int $idPages): self { $this->idPages = $idPages; return $this; }

    public function getAclSelect(): bool { return $this->aclSelect; }
    public function setAclSelect(bool $aclSelect): self { $this->aclSelect = $aclSelect; return $this; }

    public function getAclInsert(): bool { return $this->aclInsert; }
    public function setAclInsert(bool $aclInsert): self { $this->aclInsert = $aclInsert; return $this; }

    public function getAclUpdate(): bool { return $this->aclUpdate; }
    public function setAclUpdate(bool $aclUpdate): self { $this->aclUpdate = $aclUpdate; return $this; }

    public function getAclDelete(): bool { return $this->aclDelete; }
    public function setAclDelete(bool $aclDelete): self { $this->aclDelete = $aclDelete; return $this; }

    public function isAclSelect(): ?bool
    {
        return $this->aclSelect;
    }

    public function isAclInsert(): ?bool
    {
        return $this->aclInsert;
    }

    public function isAclUpdate(): ?bool
    {
        return $this->aclUpdate;
    }

    public function isAclDelete(): ?bool
    {
        return $this->aclDelete;
    }
}
// ENTITY RULE
