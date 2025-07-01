<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'scheduledJobs_notifications')]
class ScheduledJobsNotification
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: ScheduledJob::class)]
    #[ORM\JoinColumn(name: 'id_scheduledJobs', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?ScheduledJob $scheduledJob = null;

    #[ORM\Id]   
    #[ORM\ManyToOne(targetEntity: Notification::class)]
    #[ORM\JoinColumn(name: 'id_notifications', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Notification $notification = null;

    public function getScheduledJob(): ?ScheduledJob
    {
        return $this->scheduledJob;
    }

    public function setScheduledJob(?ScheduledJob $scheduledJob): self { $this->scheduledJob = $scheduledJob; return $this; }

    public function getNotification(): ?Notification
    {
        return $this->notification;
    }

    public function setNotification(?Notification $notification): self { $this->notification = $notification; return $this; }
}
// ENTITY RULE
