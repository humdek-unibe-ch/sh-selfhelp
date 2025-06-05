<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'acl_users')]
class AclUser
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_users', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Page::class)]
    #[ORM\JoinColumn(name: 'id_pages', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Page $page = null;

    public function setPage(Page $page): static
    {
        $this->page = $page;
        return $this;
    }

    #[ORM\Column(name: 'acl_select', type: 'boolean', options: ['default' => 1])]
    private bool $aclSelect = true;

    #[ORM\Column(name: 'acl_insert', type: 'boolean', options: ['default' => 0])]
    private bool $aclInsert = false;

    #[ORM\Column(name: 'acl_update', type: 'boolean', options: ['default' => 0])]
    private bool $aclUpdate = false;

    #[ORM\Column(name: 'acl_delete', type: 'boolean', options: ['default' => 0])]
    private bool $aclDelete = false;


    public function getAclSelect(): bool { return $this->aclSelect; }

    public function setAclSelect(bool $aclSelect): static
    {
        $this->aclSelect = $aclSelect;

        return $this;
    }

    public function getAclInsert(): bool { return $this->aclInsert; }

    public function setAclInsert(bool $aclInsert): static
    {
        $this->aclInsert = $aclInsert;

        return $this;
    }

    public function getAclUpdate(): bool { return $this->aclUpdate; }

    public function setAclUpdate(bool $aclUpdate): static
    {
        $this->aclUpdate = $aclUpdate;

        return $this;
    }

    public function getAclDelete(): bool { return $this->aclDelete; }

    public function setAclDelete(bool $aclDelete): static
    {
        $this->aclDelete = $aclDelete;

        return $this;
    }

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
