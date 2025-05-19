<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'groups')]
class Group
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: 'string', length: 100)]
    private string $name;

    #[ORM\Column(name: 'description', type: 'string', length: 250)]
    private string $description;

    #[ORM\Column(name: 'id_group_types', type: 'integer', nullable: true)]
    private ?int $idGroupTypes = null;

    #[ORM\Column(name: 'requires_2fa', type: 'boolean')]
    private bool $requires2fa = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getIdGroupTypes(): ?int
    {
        return $this->idGroupTypes;
    }

    public function setIdGroupTypes(?int $idGroupTypes): static
    {
        $this->idGroupTypes = $idGroupTypes;

        return $this;
    }

    public function isRequires2fa(): ?bool
    {
        return $this->requires2fa;
    }

    public function setRequires2fa(bool $requires2fa): static
    {
        $this->requires2fa = $requires2fa;

        return $this;
    }
}
// ENTITY RULE
