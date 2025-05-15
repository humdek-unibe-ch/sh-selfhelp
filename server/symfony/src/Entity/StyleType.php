<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StyleTypeRepository")
 * @ORM\Table(name="styleType")
 */
class StyleType
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id", options={"unsigned": true, "zerofill": true})
     * COLUMN: id
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, nullable=false, name="name")
     * COLUMN: name
     */
    private $name;

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
}
// ENTITY RULE
