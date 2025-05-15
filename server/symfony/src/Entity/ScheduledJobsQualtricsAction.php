<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'scheduledJobs_qualtricsActions')]
class ScheduledJobsQualtricsAction
{
    #[ORM\Id]
    #[ORM\Column(name: 'id_scheduledJobs', type: 'integer')]
    private int $idScheduledJobs;

    #[ORM\Id]
    #[ORM\Column(name: 'id_qualtricsActions', type: 'integer')]
    private int $idQualtricsActions;

    public function getIdScheduledJobs(): int { return $this->idScheduledJobs; }
    public function setIdScheduledJobs(int $idScheduledJobs): self { $this->idScheduledJobs = $idScheduledJobs; return $this; }
    public function getIdQualtricsActions(): int { return $this->idQualtricsActions; }
    public function setIdQualtricsActions(int $idQualtricsActions): self { $this->idQualtricsActions = $idQualtricsActions; return $this; }
}
// ENTITY RULE
