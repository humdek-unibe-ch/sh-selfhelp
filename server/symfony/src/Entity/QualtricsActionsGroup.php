<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'qualtricsActions_groups')]
class QualtricsActionsGroup
{
    #[ORM\Id]
    #[ORM\Column(name: 'id_qualtricsActions', type: 'integer')]
    private int $idQualtricsActions;

    #[ORM\Id]
    #[ORM\Column(name: 'id_groups', type: 'integer')]
    private int $idGroups;

    #[ORM\ManyToOne(targetEntity: QualtricsAction::class)]
    #[ORM\JoinColumn(name: 'id_qualtricsActions', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?QualtricsAction $qualtricsAction = null;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'id_groups', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Group $group = null;

    public function getIdQualtricsActions(): int { return $this->idQualtricsActions; }
    public function setIdQualtricsActions(int $idQualtricsActions): self { $this->idQualtricsActions = $idQualtricsActions; return $this; }
    public function getIdGroups(): int { return $this->idGroups; }
    public function setIdGroups(int $idGroups): self { $this->idGroups = $idGroups; return $this; }
    public function getQualtricsAction(): ?QualtricsAction { return $this->qualtricsAction; }
    public function setQualtricsAction(?QualtricsAction $qualtricsAction): self { $this->qualtricsAction = $qualtricsAction; return $this; }
    public function getGroup(): ?Group { return $this->group; }
    public function setGroup(?Group $group): self { $this->group = $group; return $this; }
}
// ENTITY RULE
