<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'sections_hierarchy')]
class SectionsHierarchy
{

    #[ORM\Column(name: 'position', type: 'integer', nullable: true)]
    private ?int $position = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Section::class)]
    #[ORM\JoinColumn(name: 'parent', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Section $parentSection = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Section::class)]
    #[ORM\JoinColumn(name: 'child', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Section $childSection = null;

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getParentSection(): ?Section
    {
        return $this->parentSection;
    }

    public function setParentSection(?Section $parentSection): static
    {
        $this->parentSection = $parentSection;

        return $this;
    }

    public function getChildSection(): ?Section
    {
        return $this->childSection;
    }

    public function setChildSection(?Section $childSection): static
    {
        $this->childSection = $childSection;

        return $this;
    }
}
// ENTITY RULE
