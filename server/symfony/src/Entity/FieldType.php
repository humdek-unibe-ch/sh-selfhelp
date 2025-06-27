<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'fieldType')]
class FieldType
{
    #[ORM\OneToMany(mappedBy: 'type', targetEntity: Field::class)]
    private ?\Doctrine\Common\Collections\Collection $fields = null;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: 'string', length: 100, unique: true)]
    private string $name;

    #[ORM\Column(name: 'position', type: 'integer')]
    private int $position;

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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getFields(): ?\Doctrine\Common\Collections\Collection
    {
        return $this->fields;
    }

    public function addField(Field $field): static
    {
        if (!$this->fields) {
            $this->fields = new \Doctrine\Common\Collections\ArrayCollection();
        }
        if (!$this->fields->contains($field)) {
            $this->fields[] = $field;
            $field->setType($this);
        }
        return $this;
    }

    public function removeField(Field $field): static
    {
        if ($this->fields && $this->fields->contains($field)) {
            $this->fields->removeElement($field);
            if ($field->getType() === $this) {
                $field->setType(null);
            }
        }
        return $this;
    }
}
// ENTITY RULE
