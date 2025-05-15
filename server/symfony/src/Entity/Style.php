<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\StyleRepository")]
#[ORM\Table(name: 'styles')]
class Style
{
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

    #[ORM\ManyToOne(targetEntity: StyleType::class)]
    #[ORM\JoinColumn(name: 'id_type', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?StyleType $type = null;

    #[ORM\ManyToOne(targetEntity: StyleGroup::class)]
    #[ORM\JoinColumn(name: 'id_group', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?StyleGroup $group = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getIdType(): int
    {
        return $this->idType;
    }

    public function setIdType(int $idType): self
    {
        $this->idType = $idType;
        return $this;
    }

    public function getIdGroup(): int
    {
        return $this->idGroup;
    }

    public function setIdGroup(int $idGroup): self
    {
        $this->idGroup = $idGroup;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getType(): ?StyleType
    {
        return $this->type;
    }

    public function setType(?StyleType $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getGroup(): ?StyleGroup
    {
        return $this->group;
    }

    public function setGroup(?StyleGroup $group): self
    {
        $this->group = $group;
        return $this;
    }
}
// ENTITY RULE