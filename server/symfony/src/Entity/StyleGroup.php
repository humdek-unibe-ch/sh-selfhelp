<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\StyleGroupRepository")]
#[ORM\Table(name: 'styleGroup')]
#[ORM\UniqueConstraint(name: 'styleGroup_name', columns: ['name'])]
class StyleGroup
{
    #[ORM\OneToMany(mappedBy: 'group', targetEntity: Style::class)]
    private ?\Doctrine\Common\Collections\Collection $styles = null;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: 'string', length: 100, unique: true)]
    private string $name;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'position', type: 'integer', nullable: true)]
    private ?int $position = null;

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

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getStyles(): ?\Doctrine\Common\Collections\Collection
    {
        return $this->styles;
    }

    public function addStyle(Style $style): static
    {
        if (!$this->styles) {
            $this->styles = new \Doctrine\Common\Collections\ArrayCollection();
        }
        if (!$this->styles->contains($style)) {
            $this->styles[] = $style;
            $style->setGroup($this);
        }
        return $this;
    }

    public function removeStyle(Style $style): static
    {
        if ($this->styles && $this->styles->contains($style)) {
            $this->styles->removeElement($style);
            if ($style->getGroup() === $this) {
                $style->setGroup(null);
            }
        }
        return $this;
    }
}
// ENTITY RULE
