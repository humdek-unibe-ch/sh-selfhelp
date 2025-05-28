<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use App\Entity\Group;
use App\Entity\ApiRoute;

#[ORM\Entity]
#[ORM\Table(name: 'acl_group_api_routes')]
#[ORM\Index(columns: ['id_groups'], name: 'IDX_acl_group_api_routes_group')]
#[ORM\Index(columns: ['id_api_routes'], name: 'IDX_acl_group_api_routes_route')]
class AclGroupApiRoutes
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'id_groups', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Group $group = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: ApiRoute::class)]
    #[ORM\JoinColumn(name: 'id_api_routes', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?ApiRoute $apiRoute = null;

    #[ORM\Column(name: 'acl_select', type: 'boolean', options: ['default' => 0])]
    private bool $aclSelect = false;

    #[ORM\Column(name: 'acl_insert', type: 'boolean', options: ['default' => 0])]
    private bool $aclInsert = false;

    #[ORM\Column(name: 'acl_update', type: 'boolean', options: ['default' => 0])]
    private bool $aclUpdate = false;

    #[ORM\Column(name: 'acl_delete', type: 'boolean', options: ['default' => 0])]
    private bool $aclDelete = false;

    /**
     * Get the group
     */
    public function getGroup(): ?Group
    {
        return $this->group;
    }

    /**
     * Set the group
     */
    public function setGroup(?Group $group): self
    {
        $this->group = $group;
        return $this;
    }

    /**
     * Get the API route
     */
    public function getApiRoute(): ?ApiRoute
    {
        return $this->apiRoute;
    }

    /**
     * Set the API route
     */
    public function setApiRoute(?ApiRoute $apiRoute): self
    {
        $this->apiRoute = $apiRoute;
        return $this;
    }

    /**
     * Get the ACL select permission
     */
    public function getAclSelect(): bool
    {
        return $this->aclSelect;
    }

    /**
     * Set the ACL select permission
     */
    public function setAclSelect(bool $aclSelect): self
    {
        $this->aclSelect = $aclSelect;
        return $this;
    }

    /**
     * Get the ACL insert permission
     */
    public function getAclInsert(): bool
    {
        return $this->aclInsert;
    }

    /**
     * Set the ACL insert permission
     */
    public function setAclInsert(bool $aclInsert): self
    {
        $this->aclInsert = $aclInsert;
        return $this;
    }

    /**
     * Get the ACL update permission
     */
    public function getAclUpdate(): bool
    {
        return $this->aclUpdate;
    }

    /**
     * Set the ACL update permission
     */
    public function setAclUpdate(bool $aclUpdate): self
    {
        $this->aclUpdate = $aclUpdate;
        return $this;
    }

    /**
     * Get the ACL delete permission
     */
    public function getAclDelete(): bool
    {
        return $this->aclDelete;
    }

    /**
     * Set the ACL delete permission
     */
    public function setAclDelete(bool $aclDelete): self
    {
        $this->aclDelete = $aclDelete;
        return $this;
    }

    /**
     * Check if select permission is granted
     */
    public function isAclSelect(): bool
    {
        return $this->aclSelect;
    }

    /**
     * Check if insert permission is granted
     */
    public function isAclInsert(): bool
    {
        return $this->aclInsert;
    }

    /**
     * Check if update permission is granted
     */
    public function isAclUpdate(): bool
    {
        return $this->aclUpdate;
    }

    /**
     * Check if delete permission is granted
     */
    public function isAclDelete(): bool
    {
        return $this->aclDelete;
    }
}
// ENTITY RULE