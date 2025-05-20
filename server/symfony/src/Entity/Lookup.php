<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: "App\Repository\LookupRepository")]
#[ORM\Table(name: 'lookups')]
class Lookup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'type_code', type: 'string', length: 100)]
    private string $typeCode;

    #[ORM\Column(name: 'lookup_code', type: 'string', length: 100, nullable: true)]
    private ?string $lookupCode = null;

    #[ORM\Column(name: 'lookup_value', type: 'string', length: 200, nullable: true)]
    private ?string $lookupValue = null;

    #[ORM\Column(name: 'lookup_description', type: 'string', length: 500, nullable: true)]
    private ?string $lookupDescription = null;




    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeCode(): string
    {
        return $this->typeCode;
    }

    public function setTypeCode(string $typeCode): static
    {
        $this->typeCode = $typeCode;
        return $this;
    }

    public function getLookupCode(): ?string
    {
        return $this->lookupCode;
    }

    public function setLookupCode(?string $lookupCode): static
    {
        $this->lookupCode = $lookupCode;
        return $this;
    }

    public function getLookupValue(): ?string
    {
        return $this->lookupValue;
    }

    public function setLookupValue(?string $lookupValue): static
    {
        $this->lookupValue = $lookupValue;
        return $this;
    }

    public function getLookupDescription(): ?string
    {
        return $this->lookupDescription;
    }

    public function setLookupDescription(?string $lookupDescription): static
    {
        $this->lookupDescription = $lookupDescription;
        return $this;
    }
}
