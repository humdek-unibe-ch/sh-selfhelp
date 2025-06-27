<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'fields')]
class Field
{
    #[ORM\OneToMany(mappedBy: 'field', targetEntity: StylesField::class)]
    private ?\Doctrine\Common\Collections\Collection $stylesFields = null;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: 'string', length: 100, unique: true)]
    private string $name;

    #[ORM\ManyToOne(targetEntity: FieldType::class, inversedBy: 'fields', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'id_type', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?FieldType $type = null;

    #[ORM\Column(name: 'display', type: 'boolean')]
    private bool $display = true;

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

    public function getType(): ?FieldType
    {
        return $this->type;
    }

    public function setType(?FieldType $type): static
    {
        $this->type = $type;

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
            $stylesField->setField($this);
        }
        return $this;
    }

    public function removeStylesField(StylesField $stylesField): static
    {
        if ($this->stylesFields && $this->stylesFields->contains($stylesField)) {
            $this->stylesFields->removeElement($stylesField);
            if ($stylesField->getField() === $this) {
                $stylesField->setField(null);
            }
        }
        return $this;
    }

    public function isDisplay(): ?bool
    {
        return $this->display;
    }

    public function setDisplay(bool $display): static
    {
        $this->display = $display;

        return $this;
    }
}
// ENTITY RULE
