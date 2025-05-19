<?php

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'pages')]
class Page
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'keyword', type: 'string', length: 100, unique: true)]
    private ?string $keyword = null;

    #[ORM\Column(name: 'url', type: 'string', length: 255, nullable: true)]
    private ?string $url = null;

    #[ORM\Column(name: 'protocol', type: 'string', length: 100, nullable: true, options: ['comment' => 'pipe separated list of HTTP Methods (GET|POST)'])]
    private ?string $protocol = null;

    #[ORM\Column(name: 'id_actions', type: 'integer', nullable: true)]
    private ?int $id_actions = null;

    #[ORM\Column(name: 'id_navigation_section', type: 'integer', nullable: true)]
    private ?int $id_navigation_section = null;

    #[ORM\Column(name: 'parent', type: 'integer', nullable: true)]
    private ?int $parent = null;

    #[ORM\Column(name: 'is_headless', type: 'boolean', options: ['default' => 0])]
    private bool $is_headless = false;

    #[ORM\Column(name: 'nav_position', type: 'integer', nullable: true)]
    private ?int $nav_position = null;

    #[ORM\Column(name: 'footer_position', type: 'integer', nullable: true)]
    private ?int $footer_position = null;

    #[ORM\Column(name: 'id_type', type: 'integer')]
    private int $id_type;

    #[ORM\Column(name: 'id_pageAccessTypes', type: 'integer', nullable: true)]
    private ?int $id_pageAccessTypes = null;

    #[ORM\Column(name: 'is_open_access', type: 'boolean', options: ['default' => 0], nullable: true)]
    private ?bool $is_open_access = false;

    #[ORM\Column(name: 'is_system', type: 'boolean', options: ['default' => 0], nullable: true)]
    private ?bool $is_system = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKeyword(): ?string
    {
        return $this->keyword;
    }

    public function setKeyword(string $keyword): static
    {
        $this->keyword = $keyword;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getProtocol(): ?string
    {
        return $this->protocol;
    }

    public function setProtocol(?string $protocol): static
    {
        $this->protocol = $protocol;

        return $this;
    }

    public function getIdActions(): ?int
    {
        return $this->id_actions;
    }

    public function setIdActions(?int $id_actions): static
    {
        $this->id_actions = $id_actions;

        return $this;
    }

    public function getIdNavigationSection(): ?int
    {
        return $this->id_navigation_section;
    }

    public function setIdNavigationSection(?int $id_navigation_section): static
    {
        $this->id_navigation_section = $id_navigation_section;

        return $this;
    }

    public function getParent(): ?int
    {
        return $this->parent;
    }

    public function setParent(?int $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    public function isHeadless(): ?bool
    {
        return $this->is_headless;
    }

    public function setIsHeadless(bool $is_headless): static
    {
        $this->is_headless = $is_headless;

        return $this;
    }

    public function getNavPosition(): ?int
    {
        return $this->nav_position;
    }

    public function setNavPosition(?int $nav_position): static
    {
        $this->nav_position = $nav_position;

        return $this;
    }

    public function getFooterPosition(): ?int
    {
        return $this->footer_position;
    }

    public function setFooterPosition(?int $footer_position): static
    {
        $this->footer_position = $footer_position;

        return $this;
    }

    public function getIdType(): ?int
    {
        return $this->id_type;
    }

    public function setIdType(int $id_type): static
    {
        $this->id_type = $id_type;

        return $this;
    }

    public function getIdPageAccessTypes(): ?int
    {
        return $this->id_pageAccessTypes;
    }

    public function setIdPageAccessTypes(?int $id_pageAccessTypes): static
    {
        $this->id_pageAccessTypes = $id_pageAccessTypes;

        return $this;
    }

    public function isOpenAccess(): ?bool
    {
        return $this->is_open_access;
    }

    public function setIsOpenAccess(?bool $is_open_access): static
    {
        $this->is_open_access = $is_open_access;

        return $this;
    }

    public function isSystem(): ?bool
    {
        return $this->is_system;
    }

    public function setIsSystem(?bool $is_system): static
    {
        $this->is_system = $is_system;

        return $this;
    }
}
// ENTITY RULE
