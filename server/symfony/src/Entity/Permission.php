<?php

namespace App\Entity;

use App\Entity\ApiRoute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity]
#[ORM\Table(name: 'permissions')]
class Permission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 100, unique: true)]
    private string $name;

    #[ORM\Column(name: 'description', type: Types::STRING, length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'permissions')]
    #[ORM\JoinTable(name: 'roles_permissions',
        joinColumns: [new ORM\JoinColumn(name: 'id_permissions', referencedColumnName: 'id', onDelete: 'CASCADE')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'id_roles', referencedColumnName: 'id', onDelete: 'CASCADE')]
    )]
    private Collection $roles;
    
    #[ORM\ManyToMany(targetEntity: ApiRoute::class, mappedBy: 'permissions')]
    private Collection $apiRoutes;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->apiRoutes = new ArrayCollection();
    }

    /**
     * Get the permission ID
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the permission name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the permission name
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the permission description
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set the permission description
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get the roles associated with this permission
     * 
     * @return Collection<int, Role>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    /**
     * Add a role to this permission
     */
    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }

        return $this;
    }

    /**
     * Remove a role from this permission
     */
    public function removeRole(Role $role): self
    {
        $this->roles->removeElement($role);
        return $this;
    }
    
    /**
     * Get the API routes associated with this permission
     * 
     * @return Collection<int, ApiRoute>
     */
    public function getApiRoutes(): Collection
    {
        return $this->apiRoutes;
    }

    /**
     * Add an API route to this permission
     */
    public function addApiRoute(ApiRoute $apiRoute): self
    {
        if (!$this->apiRoutes->contains($apiRoute)) {
            $this->apiRoutes->add($apiRoute);
            $apiRoute->addPermission($this);
        }

        return $this;
    }

    /**
     * Remove an API route from this permission
     */
    public function removeApiRoute(ApiRoute $apiRoute): self
    {
        if ($this->apiRoutes->removeElement($apiRoute)) {
            $apiRoute->removePermission($this);
        }
        
        return $this;
    }
}
// ENTITY RULE
