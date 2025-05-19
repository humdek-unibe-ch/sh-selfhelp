<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\StyleTypeRepository")]
#[ORM\Table(name: 'styleType')]
class StyleType
{
    #[ORM\OneToMany(mappedBy: 'type', targetEntity: Style::class)]
    private ?\Doctrine\Common\Collections\Collection $styles = null;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer', options: ['unsigned' => true, 'zerofill' => true])]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: 'string', length: 100)]
    private string $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getStyles(): ?\Doctrine\Common\Collections\Collection
    {
        return $this->styles;
    }

    public function addStyle(Style $style): static
    {
        if (!$this->styles) {
            $this->styles = new \Doctrine\Common\Collections\ArrayCollection();
        }
        if (!$this->styles->contains($style)) {
            $this->styles[] = $style;
            $style->setType($this);
        }
        return $this;
    }

    public function removeStyle(Style $style): static
    {
        if ($this->styles && $this->styles->contains($style)) {
            $this->styles->removeElement($style);
            if ($style->getType() === $this) {
                $style->setType(null);
            }
        }
        return $this;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
// ENTITY RULE
