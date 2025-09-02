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

    #[ORM\Column(name: 'debug', type: 'boolean', nullable: false, options: ['default' => 0])]
    private bool $debug = false;

    #[ORM\Column(name: 'condition', type: 'text', nullable: true)]
    private ?string $condition = null;

    #[ORM\Column(name: 'data_config', type: 'text', nullable: true)]
    private ?string $dataConfig = null;

    #[ORM\Column(name: 'css', type: 'text', nullable: true)]
    private ?string $css = null;

    #[ORM\Column(name: 'css_mobile', type: 'text', nullable: true)]
    private ?string $cssMobile = null;

    #[ORM\ManyToOne(targetEntity: Style::class)]
    #[ORM\JoinColumn(name: 'id_styles', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Style $style = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdStyles(): ?int
    {
        return $this->idStyles;
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

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function setDebug(bool $debug): static
    {
        $this->debug = $debug;

        return $this;
    }

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    public function setCondition(?string $condition): static
    {
        $this->condition = $condition;

        return $this;
    }

    public function getDataConfig(): ?string
    {
        return $this->dataConfig;
    }

    public function setDataConfig(?string $dataConfig): static
    {
        $this->dataConfig = $dataConfig;

        return $this;
    }

    public function getCss(): ?string
    {
        return $this->css;
    }

    public function setCss(?string $css): static
    {
        $this->css = $css;

        return $this;
    }

    public function getCssMobile(): ?string
    {
        return $this->cssMobile;
    }

    public function setCssMobile(?string $cssMobile): static
    {
        $this->cssMobile = $cssMobile;

        return $this;
    }

    public function getStyle(): ?Style
    {
        return $this->style;
    }

    public function setStyle(?Style $style): static
    {
        $this->style = $style;

        return $this;
    }
}
// ENTITY RULE
