<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\StyleRepository")]
#[ORM\Table(name: 'styles')]
class Style
{
    public function __construct()
    {
        $this->stylesFields = new \Doctrine\Common\Collections\ArrayCollection();
        $this->allowedChildrenRelationships = new \Doctrine\Common\Collections\ArrayCollection();
        $this->allowedParentsRelationships = new \Doctrine\Common\Collections\ArrayCollection();
    }
    #[ORM\OneToMany(mappedBy: 'style', targetEntity: StylesField::class, cascade: ['persist', 'remove'])]
    private \Doctrine\Common\Collections\Collection $stylesFields;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: 'string', length: 100, unique: true)]
    private string $name;

    #[ORM\Column(name: 'can_have_children', type: 'boolean', options: ['default' => 0])]
    private bool $canHaveChildren = false;

    #[ORM\Column(name: 'id_group', type: 'integer')]
    private int $idGroup;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: StyleGroup::class, inversedBy: 'styles')]
    #[ORM\JoinColumn(name: 'id_group', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?StyleGroup $group = null;

    #[ORM\OneToMany(mappedBy: 'parentStyle', targetEntity: StylesAllowedRelationship::class, cascade: ['persist', 'remove'])]
    private \Doctrine\Common\Collections\Collection $allowedChildrenRelationships;

    #[ORM\OneToMany(mappedBy: 'childStyle', targetEntity: StylesAllowedRelationship::class, cascade: ['persist', 'remove'])]
    private \Doctrine\Common\Collections\Collection $allowedParentsRelationships;

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


    public function getIdGroup(): ?int
    {
        return $this->idGroup;
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

    public function getCanHaveChildren(): ?bool
    {
        return $this->canHaveChildren;
    }

    public function setCanHaveChildren(bool $canHaveChildren): static
    {
        $this->canHaveChildren = $canHaveChildren;

        return $this;
    }


    public function getGroup(): ?StyleGroup
    {
        return $this->group;
    }

    public function setGroup(?StyleGroup $group): static
    {
        $this->group = $group;

        return $this;
    }

    public function getStylesFields(): ?\Doctrine\Common\Collections\Collection
    {
        return $this->stylesFields;
    }

    public function addStylesField(StylesField $stylesField): static
    {
        if (!$this->stylesFields) {
            $this->stylesFields = new \Doctrine\Common\Collections\ArrayCollection();
        }
        if (!$this->stylesFields->contains($stylesField)) {
            $this->stylesFields[] = $stylesField;
            $stylesField->setStyle($this);
        }
        return $this;
    }

    public function removeStylesField(StylesField $stylesField): static
    {
        if ($this->stylesFields && $this->stylesFields->contains($stylesField)) {
            $this->stylesFields->removeElement($stylesField);
            if ($stylesField->getStyle() === $this) {
                $stylesField->setStyle(null);
            }
        }
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection<int, StylesAllowedRelationship>
     */
    public function getAllowedChildrenRelationships(): \Doctrine\Common\Collections\Collection
    {
        return $this->allowedChildrenRelationships;
    }

    public function addAllowedChildrenRelationship(StylesAllowedRelationship $relationship): static
    {
        if (!$this->allowedChildrenRelationships->contains($relationship)) {
            $this->allowedChildrenRelationships->add($relationship);
            $relationship->setParentStyle($this);
        }

        return $this;
    }

    public function removeAllowedChildrenRelationship(StylesAllowedRelationship $relationship): static
    {
        if ($this->allowedChildrenRelationships->removeElement($relationship)) {
            // set the owning side to null (unless already changed)
            if ($relationship->getParentStyle() === $this) {
                $relationship->setParentStyle(null);
            }
        }

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection<int, StylesAllowedRelationship>
     */
    public function getAllowedParentsRelationships(): \Doctrine\Common\Collections\Collection
    {
        return $this->allowedParentsRelationships;
    }

    public function addAllowedParentsRelationship(StylesAllowedRelationship $relationship): static
    {
        if (!$this->allowedParentsRelationships->contains($relationship)) {
            $this->allowedParentsRelationships->add($relationship);
            $relationship->setChildStyle($this);
        }

        return $this;
    }

    public function removeAllowedParentsRelationship(StylesAllowedRelationship $relationship): static
    {
        if ($this->allowedParentsRelationships->removeElement($relationship)) {
            // set the owning side to null (unless already changed)
            if ($relationship->getChildStyle() === $this) {
                $relationship->setChildStyle(null);
            }
        }

        return $this;
    }
}
// ENTITY RULE