<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'sections_navigation')]
class SectionsNavigation
{
    #[ORM\Id]
    #[ORM\Column(name: 'parent', type: 'integer')]
    private int $parent;

    #[ORM\Id]
    #[ORM\Column(name: 'child', type: 'integer')]
    private int $child;

    #[ORM\Column(name: 'id_pages', type: 'integer')]
    private int $idPages;

    #[ORM\Column(name: 'position', type: 'integer')]
    private int $position;

    #[ORM\ManyToOne(targetEntity: Section::class)]
    #[ORM\JoinColumn(name: 'parent', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Section $parentSection = null;

    #[ORM\ManyToOne(targetEntity: Section::class)]
    #[ORM\JoinColumn(name: 'child', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Section $childSection = null;

    #[ORM\ManyToOne(targetEntity: Page::class)]
    #[ORM\JoinColumn(name: 'id_pages', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Page $page = null;

    public function getParent(): ?int
    {
        return $this->parent;
    }
    public function setParent(int $parent): self { $this->parent = $parent; return $this; }

    public function getChild(): ?int
    {
        return $this->child;
    }
    public function setChild(int $child): self { $this->child = $child; return $this; }

    public function getIdPages(): ?int
    {
        return $this->idPages;
    }

    public function setIdPages(int $idPages): static
    {
        $this->idPages = $idPages;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
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

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page): static
    {
        $this->page = $page;

        return $this;
    }
}
// ENTITY RULE
