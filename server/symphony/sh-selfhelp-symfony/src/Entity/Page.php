<?php

namespace App\Entity;

use App\Repository\PageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Page entity representing CMS pages
 */
#[ORM\Entity(repositoryClass: PageRepository::class)]
#[ORM\Table(name: 'pages')]
class Page
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100, unique: true, nullable: false)]
    private ?string $keyword = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private ?string $url = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $protocol = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    private ?int $parent = null;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isHeadless = false;

    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    private ?int $navPosition = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    private ?int $footerPosition = null;

    #[ORM\Column(type: 'integer', nullable: false, options: ['unsigned' => true, 'default' => 1])]
    private int $idType = 1;

    #[ORM\Column(type: 'integer', nullable: false, options: ['unsigned' => true, 'default' => 1])]
    private int $idPageAccessTypes = 1;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isOpenAccess = false;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isSystem = false;

    #[ORM\OneToMany(mappedBy: 'page', targetEntity: PageSection::class, orphanRemoval: true)]
    private Collection $pageSections;

    #[ORM\OneToMany(mappedBy: 'page', targetEntity: PageField::class, orphanRemoval: true)]
    private Collection $pageFields;

    public function __construct()
    {
        $this->pageSections = new ArrayCollection();
        $this->pageFields = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKeyword(): ?string
    {
        return $this->keyword;
    }

    public function setKeyword(string $keyword): self
    {
        $this->keyword = $keyword;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getProtocol(): ?string
    {
        return $this->protocol;
    }

    public function setProtocol(?string $protocol): self
    {
        $this->protocol = $protocol;

        return $this;
    }

    public function getParent(): ?int
    {
        return $this->parent;
    }

    public function setParent(?int $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function isHeadless(): bool
    {
        return $this->isHeadless;
    }

    public function setIsHeadless(bool $isHeadless): self
    {
        $this->isHeadless = $isHeadless;

        return $this;
    }

    public function getNavPosition(): ?int
    {
        return $this->navPosition;
    }

    public function setNavPosition(?int $navPosition): self
    {
        $this->navPosition = $navPosition;

        return $this;
    }

    public function getFooterPosition(): ?int
    {
        return $this->footerPosition;
    }

    public function setFooterPosition(?int $footerPosition): self
    {
        $this->footerPosition = $footerPosition;

        return $this;
    }

    public function getIdType(): int
    {
        return $this->idType;
    }

    public function setIdType(int $idType): self
    {
        $this->idType = $idType;

        return $this;
    }

    public function getIdPageAccessTypes(): int
    {
        return $this->idPageAccessTypes;
    }

    public function setIdPageAccessTypes(int $idPageAccessTypes): self
    {
        $this->idPageAccessTypes = $idPageAccessTypes;

        return $this;
    }

    public function isOpenAccess(): bool
    {
        return $this->isOpenAccess;
    }

    public function setIsOpenAccess(bool $isOpenAccess): self
    {
        $this->isOpenAccess = $isOpenAccess;

        return $this;
    }

    public function isSystem(): bool
    {
        return $this->isSystem;
    }

    public function setIsSystem(bool $isSystem): self
    {
        $this->isSystem = $isSystem;

        return $this;
    }

    /**
     * @return Collection<int, PageSection>
     */
    public function getPageSections(): Collection
    {
        return $this->pageSections;
    }

    /**
     * @return Collection<int, PageField>
     */
    public function getPageFields(): Collection
    {
        return $this->pageFields;
    }

}