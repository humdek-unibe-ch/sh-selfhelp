<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'sections')]
class Section
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'id_styles', type: 'integer')]
    private int $idStyles;

    #[ORM\Column(name: 'name', type: 'string', length: 100)]
    private string $name;

    #[ORM\ManyToOne(targetEntity: Style::class)]
    #[ORM\JoinColumn(name: 'id_styles', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Style $style = null;

    public function getId(): ?int { return $this->id; }
    public function getIdStyles(): int { return $this->idStyles; }
    public function setIdStyles(int $idStyles): self { $this->idStyles = $idStyles; return $this; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getStyle(): ?Style { return $this->style; }
    public function setStyle(?Style $style): self { $this->style = $style; return $this; }
}
// ENTITY RULE
