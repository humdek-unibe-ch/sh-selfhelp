<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'scheduledJobs_tasks')]
class ScheduledJobsTask
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: ScheduledJob::class)]
    #[ORM\JoinColumn(name: 'id_scheduledJobs', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?ScheduledJob $scheduledJob = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Task::class)]
    #[ORM\JoinColumn(name: 'id_tasks', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Task $task = null;

    public function getScheduledJob(): ?ScheduledJob
    {
        return $this->scheduledJob;
    }

    public function setScheduledJob(?ScheduledJob $scheduledJob): self { $this->scheduledJob = $scheduledJob; return $this; }

    public function getTask(): ?Task
    {
        return $this->task;
    }

    public function setTask(?Task $task): static
    {
        $this->task = $task;

        return $this;
    }

}
// ENTITY RULE
