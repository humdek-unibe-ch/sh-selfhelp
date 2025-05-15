<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'qualtricsActions_functions')]
class QualtricsActionsFunction
{
    #[ORM\Id]
    #[ORM\Column(name: 'id_qualtricsActions', type: 'integer')]
    private int $idQualtricsActions;

    #[ORM\Id]
    #[ORM\Column(name: 'id_lookups', type: 'integer')]
    private int $idLookups;

    #[ORM\ManyToOne(targetEntity: QualtricsAction::class)]
    #[ORM\JoinColumn(name: 'id_qualtricsActions', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?QualtricsAction $qualtricsAction = null;

    #[ORM\ManyToOne(targetEntity: Lookup::class)]
    #[ORM\JoinColumn(name: 'id_lookups', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Lookup $lookup = null;

    public function getIdQualtricsActions(): int { return $this->idQualtricsActions; }
    public function setIdQualtricsActions(int $idQualtricsActions): self { $this->idQualtricsActions = $idQualtricsActions; return $this; }
    public function getIdLookups(): int { return $this->idLookups; }
    public function setIdLookups(int $idLookups): self { $this->idLookups = $idLookups; return $this; }
    public function getQualtricsAction(): ?QualtricsAction { return $this->qualtricsAction; }
    public function setQualtricsAction(?QualtricsAction $qualtricsAction): self { $this->qualtricsAction = $qualtricsAction; return $this; }
    public function getLookup(): ?Lookup { return $this->lookup; }
    public function setLookup(?Lookup $lookup): self { $this->lookup = $lookup; return $this; }
}
// ENTITY RULE
