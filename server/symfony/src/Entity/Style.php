<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StyleRepository")
 * @ORM\Table(name="styles",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="styles_name", columns={"name"})},
 *     indexes={
 *         @ORM\Index(name="id_type", columns={"id_type"}),
 *         @ORM\Index(name="id_group", columns={"id_group"})
 *     }
 * )
 */
class Style
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id", options={"unsigned": true, "zerofill": true})
     * COLUMN: id
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, nullable=false, unique=true, name="name")
     * COLUMN: name
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\\Entity\\StyleType")
     * @ORM\JoinColumn(name="id_type", referencedColumnName="id", nullable=false, onDelete="CASCADE", onUpdate="CASCADE")
     * COLUMN: id_type
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="App\\Entity\\StyleGroup")
     * @ORM\JoinColumn(name="id_group", referencedColumnName="id", nullable=false, onDelete="CASCADE", onUpdate="CASCADE")
     * COLUMN: id_group
     */
    private $group;

    /**
     * @ORM\Column(type="text", nullable=true, name="description")
     * COLUMN: description
     */
    private $description;

    // --- Getters and Setters ---

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }
}
// ENTITY RULE