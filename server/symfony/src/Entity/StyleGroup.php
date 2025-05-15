<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StyleGroupRepository")
 * @ORM\Table(name="styleGroup",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="styleGroup_name", columns={"name"})}
 * )
 */
class StyleGroup
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
     * @ORM\Column(type="text", nullable=true, name="description")
     * COLUMN: description
     */
    private $description;

    /**
     * @ORM\Column(type="integer", nullable=true, name="position")
     * COLUMN: position
     */
    private $position;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;
        return $this;
    }
}
// ENTITY RULE
