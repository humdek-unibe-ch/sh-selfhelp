<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'version')]
class Version
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'version', type: 'string', length: 100, nullable: true)]
    private ?string $version = null;

    public function getId(): ?int { return $this->id; }
    public function getVersion(): ?string { return $this->version; }
    public function setVersion(?string $version): self { $this->version = $version; return $this; }
}
// ENTITY RULE
