<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'plugins')]
class Plugin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: 'string', length: 100, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(name: 'version', type: 'string', length: 500, nullable: true)]
    private ?string $version = null;

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(?string $name): self { $this->name = $name; return $this; }
    public function getVersion(): ?string { return $this->version; }
    public function setVersion(?string $version): self { $this->version = $version; return $this; }
}
// ENTITY RULE
