<?php

namespace App\Entity;

use App\Repository\ApiRouteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApiRouteRepository::class)]
#[ORM\Table(name: 'api_routes')]
#[ORM\UniqueConstraint(name: 'uniq_version_path', columns: ['version', 'path'])]
#[ORM\UniqueConstraint(name: 'uniq_route_name_version', columns: ['route_name', 'version'])]
class ApiRoute
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'route_name', length: 100)]
    private ?string $route_name = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    #[ORM\Column(length: 255)]
    private ?string $controller = null;

    #[ORM\Column(length: 50)]
    private ?string $methods = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $requirements = null;

    #[ORM\Column(type: 'json', nullable: true, options: ['comment' => 'Expected parameters: name → {in: body|query, required: bool}'])]
    private ?array $params = null;

    #[ORM\Column(length: 10, options: ['default' => 'v1'])]
    private ?string $version = 'v1';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRouteName(): ?string
    {
        return $this->route_name;
    }

    public function setRouteName(string $route_name): static
    {
        $this->route_name = $route_name;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getController(): ?string
    {
        return $this->controller;
    }

    public function setController(string $controller): static
    {
        $this->controller = $controller;

        return $this;
    }

    public function getMethods(): ?string
    {
        return $this->methods;
    }

    public function setMethods(string $methods): static
    {
        $this->methods = $methods;

        return $this;
    }

    public function getRequirements(): ?array
    {
        return $this->requirements;
    }

    public function setRequirements(?array $requirements): static
    {
        $this->requirements = $requirements;

        return $this;
    }

    public function getParams(): ?array
    {
        return $this->params;
    }

    public function setParams(?array $params): static
    {
        $this->params = $params;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): static
    {
        $this->version = $version;

        return $this;
    }
}
