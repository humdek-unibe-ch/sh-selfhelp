<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Lookup;

#[ORM\Entity(repositoryClass: "App\Repository\StyleRepository")]
#[ORM\Table(name: 'styles')]
class Style
{
    public function __construct()
    {
        $this->stylesFields = new \Doctrine\Common\Collections\ArrayCollection();
    }
    #[ORM\OneToMany(mappedBy: 'style', targetEntity: StylesField::class, cascade: ['persist', 'remove'])]
    private \Doctrine\Common\Collections\Collection $stylesFields;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer', options: ['unsigned' => true, 'zerofill' => true])]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: 'string', length: 100, unique: true)]
    private string $name;

    #[ORM\Column(name: 'id_type', type: 'integer')]
    private int $idType;

    #[ORM\Column(name: 'id_group', type: 'integer')]
    private int $idGroup;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: StyleType::class, inversedBy: 'styles')]
    #[ORM\JoinColumn(name: 'idType', referencedColumnName: 'id', nullable: false)]
    private ?StyleType $type = null;

    #[ORM\ManyToOne(targetEntity: StyleGroup::class, inversedBy: 'styles')]
    #[ORM\JoinColumn(name: 'id_group', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?StyleGroup $group = null;

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

    public function getIdType(): ?int
    {
        return $this->idType;
    }

    public function setIdType(int $idType): static
    {
        $this->idType = $idType;

        return $this;
    }

    public function getIdGroup(): ?int
    {
        return $this->idGroup;
    }

    public function setIdGroup(int $idGroup): static
    {
        $this->idGroup = $idGroup;

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

    public function getType(): ?StyleType
    {
        return $this->type;
    }

    public function setType(?StyleType $type): static
    {
        $this->type = $type;
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
}
// ENTITY RULE