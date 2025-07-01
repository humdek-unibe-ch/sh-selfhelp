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
    #[ORM\ManyToOne(targetEntity: ValidationCode::class)]
    #[ORM\JoinColumn(name: 'code', referencedColumnName: 'code', onDelete: 'CASCADE')]
    private ?ValidationCode $code = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'id_groups', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Group $group = null;

    public function getCode(): ?ValidationCode
    {
        return $this->code;
    }
    public function setCode(ValidationCode $code): self { $this->code = $code; return $this; }

    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function setGroup(Group $group): self { $this->group = $group; return $this; }
}
// ENTITY RULE
