<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'scheduledJobs_tasks')]
class ScheduledJobsTask
{
    #[ORM\Id]
    #[ORM\Column(name: 'id_scheduledJobs', type: 'integer')]
    private int $idScheduledJobs;

    #[ORM\Id]
    #[ORM\Column(name: 'id_tasks', type: 'integer')]
    private int $idTasks;

    #[ORM\ManyToOne(targetEntity: ScheduledJob::class)]
    #[ORM\JoinColumn(name: 'id_scheduledJobs', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?ScheduledJob $scheduledJob = null;

    #[ORM\ManyToOne(targetEntity: Task::class)]
    #[ORM\JoinColumn(name: 'id_tasks', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Task $task = null;

    public function getIdScheduledJobs(): int { return $this->idScheduledJobs; }
    public function setIdScheduledJobs(int $idScheduledJobs): self { $this->idScheduledJobs = $idScheduledJobs; return $this; }
    public function getIdTasks(): int { return $this->idTasks; }
    public function setIdTasks(int $idTasks): self { $this->idTasks = $idTasks; return $this; }
    public function getScheduledJob(): ?ScheduledJob { return $this->scheduledJob; }
    public function setScheduledJob(?ScheduledJob $scheduledJob): self { $this->scheduledJob = $scheduledJob; return $this; }
    public function getTask(): ?Task { return $this->task; }
    public function setTask(?Task $task): self { $this->task = $task; return $this; }
}
// ENTITY RULE
