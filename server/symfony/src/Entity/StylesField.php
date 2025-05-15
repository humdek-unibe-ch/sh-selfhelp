<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'styles_fields')]
class StylesField
{
    #[ORM\Id]
    #[ORM\Column(name: 'id_styles', type: 'integer')]
    private int $idStyles;

    #[ORM\Id]
    #[ORM\Column(name: 'id_fields', type: 'integer')]
    private int $idFields;

    #[ORM\Column(name: 'default_value', type: 'string', length: 100, nullable: true)]
    private ?string $defaultValue = null;

    #[ORM\Column(name: 'help', type: 'text', nullable: true)]
    private ?string $help = null;

    #[ORM\Column(name: 'disabled', type: 'boolean')]
    private bool $disabled = false;

    #[ORM\Column(name: 'hidden', type: 'integer', nullable: true)]
    private ?int $hidden = 0;

    #[ORM\ManyToOne(targetEntity: Style::class)]
    #[ORM\JoinColumn(name: 'id_styles', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Style $style = null;

    #[ORM\ManyToOne(targetEntity: Field::class)]
    #[ORM\JoinColumn(name: 'id_fields', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Field $field = null;

    public function getIdStyles(): int { return $this->idStyles; }
    public function setIdStyles(int $idStyles): self { $this->idStyles = $idStyles; return $this; }
    public function getIdFields(): int { return $this->idFields; }
    public function setIdFields(int $idFields): self { $this->idFields = $idFields; return $this; }
    public function getDefaultValue(): ?string { return $this->defaultValue; }
    public function setDefaultValue(?string $defaultValue): self { $this->defaultValue = $defaultValue; return $this; }
    public function getHelp(): ?string { return $this->help; }
    public function setHelp(?string $help): self { $this->help = $help; return $this; }
    public function isDisabled(): bool { return $this->disabled; }
    public function setDisabled(bool $disabled): self { $this->disabled = $disabled; return $this; }
    public function getHidden(): ?int { return $this->hidden; }
    public function setHidden(?int $hidden): self { $this->hidden = $hidden; return $this; }
    public function getStyle(): ?Style { return $this->style; }
    public function setStyle(?Style $style): self { $this->style = $style; return $this; }
    public function getField(): ?Field { return $this->field; }
    public function setField(?Field $field): self { $this->field = $field; return $this; }
}
// ENTITY RULE
