<?php

namespace App\Entity;

use App\Repository\SectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Section entity representing page sections
 */
#[ORM\Entity(repositoryClass: SectionRepository::class)]
#[ORM\Table(name: 'sections')]
class Section
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: Style::class)]
    #[ORM\JoinColumn(name: 'id_styles', referencedColumnName: 'id', nullable: false)]
    private ?Style $style = null;

    #[ORM\OneToMany(mappedBy: 'section', targetEntity: SectionField::class, orphanRemoval: true)]
    private Collection $sectionFields;

    #[ORM\OneToMany(mappedBy: 'section', targetEntity: PageSection::class, orphanRemoval: true)]
    private Collection $pageSections;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: SectionHierarchy::class, orphanRemoval: true)]
    private Collection $children;

    #[ORM\OneToMany(mappedBy: 'child', targetEntity: SectionHierarchy::class, orphanRemoval: true)]
    private Collection $parents;

    public function __construct()
    {
        $this->sectionFields = new ArrayCollection();
        $this->pageSections = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->parents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getStyle(): ?Style
    {
        return $this->style;
    }

    public function setStyle(?Style $style): self
    {
        $this->style = $style;

        return $this;
    }

    /**
     * @return Collection<int, SectionField>
     */
    public function getSectionFields(): Collection
    {
        return $this->sectionFields;
    }

    public function addSectionField(SectionField $sectionField): self
    {
        if (!$this->sectionFields->contains($sectionField)) {
            $this->sectionFields[] = $sectionField;
            $sectionField->setSection($this);
        }

        return $this;
    }

    public function removeSectionField(SectionField $sectionField): self
    {
        if ($this->sectionFields->removeElement($sectionField)) {
            // set the owning side to null (unless already changed)
            if ($sectionField->getSection() === $this) {
                $sectionField->setSection(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PageSection>
     */
    public function getPageSections(): Collection
    {
        return $this->pageSections;
    }

    public function addPageSection(PageSection $pageSection): self
    {
        if (!$this->pageSections->contains($pageSection)) {
            $this->pageSections[] = $pageSection;
            $pageSection->setSection($this);
        }

        return $this;
    }

    public function removePageSection(PageSection $pageSection): self
    {
        if ($this->pageSections->removeElement($pageSection)) {
            // set the owning side to null (unless already changed)
            if ($pageSection->getSection() === $this) {
                $pageSection->setSection(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SectionHierarchy>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * @return Collection<int, SectionHierarchy>
     */
    public function getParents(): Collection
    {
        return $this->parents;
    }
}