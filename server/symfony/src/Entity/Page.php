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

    #[ORM\ManyToOne(targetEntity: Page::class)]
    #[ORM\JoinColumn(name: 'parent', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?Page $parentPage = null;

    #[ORM\ManyToOne(targetEntity: PageType::class)]
    #[ORM\JoinColumn(name: 'id_type', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?PageType $pageType = null;

    #[ORM\ManyToOne(targetEntity: Lookup::class)]
    #[ORM\JoinColumn(name: 'id_pageAccessTypes', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?Lookup $pageAccessType = null;

    #[ORM\Column(name: 'is_headless', type: 'boolean', options: ['default' => 0])]
    private bool $is_headless = false;

    #[ORM\Column(name: 'nav_position', type: 'integer', nullable: true)]
    private ?int $nav_position = null;

    #[ORM\Column(name: 'footer_position', type: 'integer', nullable: true)]
    private ?int $footer_position = null;

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

    public function getParentPage(): ?Page
    {
        return $this->parentPage;
    }

    public function setParentPage(?Page $parentPage): static
    {
        $this->parentPage = $parentPage;
        return $this;
    }

    public function getPageType(): ?PageType
    {
        return $this->pageType;
    }

    public function setPageType(?PageType $pageType): static
    {
        $this->pageType = $pageType;
        return $this;
    }

    public function getPageAccessType(): ?Lookup
    {
        return $this->pageAccessType;
    }

    public function setPageAccessType(?Lookup $pageAccessType): static
    {
        $this->pageAccessType = $pageAccessType;
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
