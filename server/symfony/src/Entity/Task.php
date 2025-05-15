<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tasks')]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'config', type: 'text', nullable: true)]
    private ?string $config = null;

    public function getId(): ?int { return $this->id; }
    public function getConfig(): ?string { return $this->config; }
    public function setConfig(?string $config): self { $this->config = $config; return $this; }
}
// ENTITY RULE
