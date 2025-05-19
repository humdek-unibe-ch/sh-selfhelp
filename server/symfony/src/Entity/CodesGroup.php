<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'codes_groups')]
class CodesGroup
{
    /**
     * @var \Doctrine\Common\Collections\Collection<int, ValidationCode>
     */
    #[ORM\OneToMany(mappedBy: 'codesGroup', targetEntity: ValidationCode::class)]
    private \Doctrine\Common\Collections\Collection $validationCodes;

    public function __construct()
    {
        $this->validationCodes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    #[ORM\Id]
    #[ORM\Column(name: 'code', type: 'string', length: 16)]
    private string $code;

    #[ORM\Id]
    #[ORM\Column(name: 'id_groups', type: 'integer')]
    private int $idGroups;

    public function getCode(): ?string
    {
        return $this->code;
    }
    public function setCode(string $code): self { $this->code = $code; return $this; }

    public function getIdGroups(): ?int
    {
        return $this->idGroups;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|ValidationCode[]
     */
    public function getValidationCodes(): \Doctrine\Common\Collections\Collection
    {
        return $this->validationCodes;
    }

    public function addValidationCode(ValidationCode $validationCode): self
    {
        if (!$this->validationCodes->contains($validationCode)) {
            $this->validationCodes[] = $validationCode;
            $validationCode->setCodesGroup($this);
        }
        return $this;
    }

    public function removeValidationCode(ValidationCode $validationCode): self
    {
        if ($this->validationCodes->removeElement($validationCode)) {
            if ($validationCode->getCodesGroup() === $this) {
                $validationCode->setCodesGroup(null);
            }
        }
        return $this;
    }
    public function setIdGroups(int $idGroups): self { $this->idGroups = $idGroups; return $this; }
}
// ENTITY RULE
