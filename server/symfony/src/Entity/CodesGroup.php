<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'codes_groups')]
class CodesGroup
{
    public function __construct()
    {
        // Empty constructor
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

    // ValidationCode relationship removed as it now uses Group entity directly
    public function setIdGroups(int $idGroups): self { $this->idGroups = $idGroups; return $this; }
}
// ENTITY RULE
