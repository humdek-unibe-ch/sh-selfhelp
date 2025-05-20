<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

#[ORM\Entity]
#[ORM\Table(name: 'acl_groups')]
#[Index(name: "id_groups_idx", columns: ["id_groups"])]
#[Index(name: "id_pages_idx", columns: ["id_pages"])]
class AclGroup
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'id_groups', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Group $group = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Page::class)]
    #[ORM\JoinColumn(name: 'id_pages', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Page $page = null;

    #[ORM\Column(name: 'acl_select', type: 'boolean', options: ['default' => 1])]
    private bool $aclSelect = true;

    #[ORM\Column(name: 'acl_insert', type: 'boolean', options: ['default' => 0])]
    private bool $aclInsert = false;

    #[ORM\Column(name: 'acl_update', type: 'boolean', options: ['default' => 0])]
    private bool $aclUpdate = false;

    #[ORM\Column(name: 'acl_delete', type: 'boolean', options: ['default' => 0])]
    private bool $aclDelete = false;

    public function getGroup(): ?Group
    {
        return $this->group;
    }
    public function setGroup(?Group $group): self
    {
        $this->group = $group;
        return $this;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }
    public function setPage(?Page $page): self
    {
        $this->page = $page;
        return $this;
    }

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
