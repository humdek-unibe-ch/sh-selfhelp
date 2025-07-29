<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'styles_fields')]
class StylesField
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Style::class, inversedBy: 'stylesFields')]
    #[ORM\JoinColumn(name: 'id_styles', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Style $style = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Field::class, inversedBy: 'stylesFields')]
    #[ORM\JoinColumn(name: 'id_fields', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Field $field = null;

    public function getStyle(): ?Style
    {
        return $this->style;
    }

    public function setStyle(?Style $style): static
    {
        $this->style = $style;
        return $this;
    }

    #[ORM\Column(name: 'default_value', type: 'string', length: 100, nullable: true)]
    private ?string $defaultValue = null;

    #[ORM\Column(name: 'help', type: 'text', nullable: true)]
    private ?string $help = null;

    #[ORM\Column(name: 'disabled', type: 'boolean')]
    private bool $disabled = false;

    #[ORM\Column(name: 'hidden', type: 'integer', nullable: true)]
    private ?int $hidden = 0;

    #[ORM\Column(name: 'title', type: 'string', length: 100, nullable: false)]
    private string $title;

    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }

    public function setDefaultValue(?string $defaultValue): static
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    public function getHelp(): ?string
    {
        return $this->help;
    }

    public function setHelp(?string $help): static
    {
        $this->help = $help;

        return $this;
    }

    public function isDisabled(): ?bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): static
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function getHidden(): ?int
    {
        return $this->hidden;
    }

    public function setHidden(?int $hidden): static
    {
        $this->hidden = $hidden;

        return $this;
    }

    public function getField(): ?Field
    {
        return $this->field;
    }

    public function setField(?Field $field): static
    {
        $this->field = $field;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;
        return $this;
    }
}
// ENTITY RULE
