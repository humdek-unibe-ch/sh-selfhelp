<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'sections_hierarchy')]
class SectionsHierarchy
{
    #[ORM\Id]
    #[ORM\Column(name: 'parent', type: 'integer')]
    private int $parent;

    #[ORM\Id]
    #[ORM\Column(name: 'child', type: 'integer')]
    private int $child;

    #[ORM\Column(name: 'position', type: 'integer', nullable: true)]
    private ?int $position = null;

    #[ORM\ManyToOne(targetEntity: Section::class)]
    #[ORM\JoinColumn(name: 'parent', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Section $parentSection = null;

    #[ORM\ManyToOne(targetEntity: Section::class)]
    #[ORM\JoinColumn(name: 'child', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Section $childSection = null;

    public function getParent(): int { return $this->parent; }
    public function setParent(int $parent): self { $this->parent = $parent; return $this; }
    public function getChild(): int { return $this->child; }
    public function setChild(int $child): self { $this->child = $child; return $this; }
    public function getPosition(): ?int { return $this->position; }
    public function setPosition(?int $position): self { $this->position = $position; return $this; }
    public function getParentSection(): ?Section { return $this->parentSection; }
    public function setParentSection(?Section $parentSection): self { $this->parentSection = $parentSection; return $this; }
    public function getChildSection(): ?Section { return $this->childSection; }
    public function setChildSection(?Section $childSection): self { $this->childSection = $childSection; return $this; }
}
// ENTITY RULE
