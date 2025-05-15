<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Style;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SectionRepository")
 * @ORM\Table(name="sections",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"name"})},
 *     indexes={
 *         @ORM\Index(name="id_styles", columns={"id_styles"})
 *     }
 * )
 */
class Section
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id", options={"unsigned": true, "zerofill": true})
     * COLUMN: id
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\\Entity\\Style")
     * @ORM\JoinColumn(name="id_styles", referencedColumnName="id", nullable=false, onDelete="CASCADE", onUpdate="CASCADE")
     * COLUMN: id_styles
     */
    private $style;

    /**
     * @ORM\Column(type="string", length=100, nullable=false, unique=true, name="name")
     * COLUMN: name
     */
    private $name;

    // --- Getters and Setters ---

    public function getId(): ?int
    {
        return $this->id;
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