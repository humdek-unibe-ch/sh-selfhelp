<?php

namespace App\Entity;

use App\Repository\ApiRouteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApiRouteRepository::class)]
#[ORM\Table(name: 'api_routes')]
class ApiRoute
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'route_name', length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    #[ORM\Column(length: 255)]
    private ?string $controller = null;

    #[ORM\Column(length: 20)]
    private ?string $methods = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $requirements = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;
        return $this;
    }

    public function getController(): ?string
    {
        return $this->controller;
    }

    public function setController(string $controller): self
    {
        $this->controller = $controller;
        return $this;
    }

    public function getMethods(): ?string
    {
        return $this->methods;
    }

    public function setMethods(string $methods): self
    {
        $this->methods = $methods;
        return $this;
    }

    public function getRequirements(): ?string
    {
        return $this->requirements;
    }

    public function setRequirements(?string $requirements): self
    {
        $this->requirements = $requirements;
        return $this;
    }

    /**
     * Convert the requirements string to an array
     */
    public function getRequirementsArray(): ?array
    {
        if (empty($this->requirements)) {
            return null;
        }
        
        return json_decode($this->requirements, true);
    }
}
