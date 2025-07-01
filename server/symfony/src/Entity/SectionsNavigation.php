<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'sections_navigation')]
class SectionsNavigation
{

    #[ORM\Column(name: 'position', type: 'integer')]
    private int $position;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Section::class)]
    #[ORM\JoinColumn(name: 'parent', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Section $parent = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Section::class)]
    #[ORM\JoinColumn(name: 'child', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Section $child = null;

    #[ORM\ManyToOne(targetEntity: Page::class)]
    #[ORM\JoinColumn(name: 'id_pages', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Page $page = null;

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getParent(): ?Section
    {
        return $this->parent;
    }

    public function setParent(?Section $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    public function getChild(): ?Section
    {
        return $this->child;
    }

    public function setChild(?Section $child): static
    {
        $this->child = $child;

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
