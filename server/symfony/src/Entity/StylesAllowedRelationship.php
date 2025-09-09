<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\StylesAllowedRelationshipRepository")]
#[ORM\Table(
    name: "styles_allowed_relationships",
    indexes: [
        new ORM\Index(name: "IDX_757F0414DC4D59BB", columns: ["id_parent_style"]),
        new ORM\Index(name: "IDX_757F041478A9D70E", columns: ["id_child_style"]),
    ]
)]
class StylesAllowedRelationship
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Style::class)]
    #[ORM\JoinColumn(name: 'id_parent_style', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Style $parentStyle = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Style::class)]
    #[ORM\JoinColumn(name: 'id_child_style', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Style $childStyle = null;

    public function getParentStyle(): ?Style
    {
        return $this->parentStyle;
    }

    public function setParentStyle(?Style $parentStyle): static
    {
        $this->parentStyle = $parentStyle;
        return $this;
    }

    public function getChildStyle(): ?Style
    {
        return $this->childStyle;
    }

    public function setChildStyle(?Style $childStyle): static
    {
        $this->childStyle = $childStyle;
        return $this;
    }

    public function getParentStyleId(): ?int
    {
        return $this->parentStyle?->getId();
    }

    public function getChildStyleId(): ?int
    {
        return $this->childStyle?->getId();
    }
}
// ENTITY RULE
