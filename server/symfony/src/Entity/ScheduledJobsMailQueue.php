<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'scheduledJobs_mailQueue')]
class ScheduledJobsMailQueue
{
    #[ORM\Id]
    #[ORM\Column(name: 'id_scheduledJobs', type: 'integer')]
    private int $idScheduledJobs;

    #[ORM\Id]
    #[ORM\Column(name: 'id_mailQueue', type: 'integer')]
    private int $idMailQueue;

    public function getIdScheduledJobs(): int { return $this->idScheduledJobs; }
    public function setIdScheduledJobs(int $idScheduledJobs): self { $this->idScheduledJobs = $idScheduledJobs; return $this; }
    public function getIdMailQueue(): int { return $this->idMailQueue; }
    public function setIdMailQueue(int $idMailQueue): self { $this->idMailQueue = $idMailQueue; return $this; }
}
// ENTITY RULE
