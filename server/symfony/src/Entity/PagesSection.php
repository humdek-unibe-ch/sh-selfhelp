<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'pages_sections')]
class PagesSection
{
    #[ORM\Id]
    #[ORM\Column(name: 'id_pages', type: 'integer')]
    private int $idPages;

    #[ORM\Id]
    #[ORM\Column(name: 'id_sections', type: 'integer')]
    private int $idSections;

    #[ORM\Column(name: 'position', type: 'integer', nullable: true)]
    private ?int $position = null;

    #[ORM\ManyToOne(targetEntity: Page::class)]
    #[ORM\JoinColumn(name: 'id_pages', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Page $page = null;

    #[ORM\ManyToOne(targetEntity: Section::class)]
    #[ORM\JoinColumn(name: 'id_sections', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Section $section = null;

    public function getIdPages(): int { return $this->idPages; }
    public function setIdPages(int $idPages): self { $this->idPages = $idPages; return $this; }
    public function getIdSections(): int { return $this->idSections; }
    public function setIdSections(int $idSections): self { $this->idSections = $idSections; return $this; }
    public function getPosition(): ?int { return $this->position; }
    public function setPosition(?int $position): self { $this->position = $position; return $this; }
    public function getPage(): ?Page { return $this->page; }
    public function setPage(?Page $page): self { $this->page = $page; return $this; }
    public function getSection(): ?Section { return $this->section; }
    public function setSection(?Section $section): self { $this->section = $section; return $this; }
}
// ENTITY RULE
