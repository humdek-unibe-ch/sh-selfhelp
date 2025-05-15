<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'scheduledJobs_notifications')]
class ScheduledJobsNotification
{
    #[ORM\Id]
    #[ORM\Column(name: 'id_scheduledJobs', type: 'integer')]
    private int $idScheduledJobs;

    #[ORM\Id]
    #[ORM\Column(name: 'id_notifications', type: 'integer')]
    private int $idNotifications;

    public function getIdScheduledJobs(): int { return $this->idScheduledJobs; }
    public function setIdScheduledJobs(int $idScheduledJobs): self { $this->idScheduledJobs = $idScheduledJobs; return $this; }
    public function getIdNotifications(): int { return $this->idNotifications; }
    public function setIdNotifications(int $idNotifications): self { $this->idNotifications = $idNotifications; return $this; }
}
// ENTITY RULE
