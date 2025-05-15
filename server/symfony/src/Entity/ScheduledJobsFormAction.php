<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'scheduledJobs_formActions')]
class ScheduledJobsFormAction
{
    #[ORM\Id]
    #[ORM\Column(name: 'id_scheduledJobs', type: 'integer')]
    private int $idScheduledJobs;

    #[ORM\Id]
    #[ORM\Column(name: 'id_formActions', type: 'integer')]
    private int $idFormActions;

    #[ORM\Column(name: 'id_dataRows', type: 'integer', nullable: true)]
    private ?int $idDataRows = null;

    public function getIdScheduledJobs(): int { return $this->idScheduledJobs; }
    public function setIdScheduledJobs(int $idScheduledJobs): self { $this->idScheduledJobs = $idScheduledJobs; return $this; }
    public function getIdFormActions(): int { return $this->idFormActions; }
    public function setIdFormActions(int $idFormActions): self { $this->idFormActions = $idFormActions; return $this; }
    public function getIdDataRows(): ?int { return $this->idDataRows; }
    public function setIdDataRows(?int $idDataRows): self { $this->idDataRows = $idDataRows; return $this; }
}
// ENTITY RULE
