<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'scheduledJobs_mailQueue')]
class ScheduledJobsMailQueue
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: ScheduledJob::class, inversedBy: 'scheduledJobsMailQueues')]
    #[ORM\JoinColumn(name: 'id_scheduledJobs', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?ScheduledJob $scheduledJob = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: MailQueue::class, inversedBy: 'scheduledJobsMailQueues')]
    #[ORM\JoinColumn(name: 'id_mailQueue', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?MailQueue $mailQueue = null;

    public function getScheduledJob(): ?ScheduledJob
    {
        return $this->scheduledJob;
    }

    public function setScheduledJob(?ScheduledJob $scheduledJob): self
    {
        $this->scheduledJob = $scheduledJob;
        return $this;
    }

    public function getMailQueue(): ?MailQueue
    {
        return $this->mailQueue;
    }

    public function setMailQueue(?MailQueue $mailQueue): self
    {
        $this->mailQueue = $mailQueue;
        return $this;
    }
}
// ENTITY RULE
