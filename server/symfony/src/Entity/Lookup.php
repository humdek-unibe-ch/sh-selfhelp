<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'lookups')]
class Lookup
{
    #[ORM\OneToMany(mappedBy: 'type', targetEntity: Style::class)]
    private ?Collection $styles;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function __construct()
    {
        $this->styles = new ArrayCollection();
    }

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $lookup_code = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|Style[]|null
     */
    public function getStyles(): ?\Doctrine\Common\Collections\Collection
    {
        return $this->styles;
    }

    public function addStyle(?Style $style): static
    {
        if ($style && !$this->styles->contains($style)) {
            $this->styles[] = $style;
            $style->setType($this);
        }
        return $this;
    }

    public function removeStyle(?Style $style): static
    {
        if ($style && $this->styles->contains($style)) {
            $this->styles->removeElement($style);
            if ($style->getType() === $this) {
                $style->setType(null);
            }
        }
        return $this;
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

    public function getLookupCode(): ?string
    {
        return $this->lookup_code;
    }

    public function setLookupCode(?string $lookup_code): static
    {
        $this->lookup_code = $lookup_code;

        return $this;
    }
}
