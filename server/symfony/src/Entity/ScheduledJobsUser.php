<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'scheduledJobs_users')]
class ScheduledJobsUser
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'scheduledJobsUsers')]
    #[ORM\JoinColumn(name: 'id_users', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: ScheduledJob::class, inversedBy: 'scheduledJobsUsers')]
    #[ORM\JoinColumn(name: 'id_scheduledJobs', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?ScheduledJob $scheduledJob = null;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getScheduledJob(): ?ScheduledJob
    {
        return $this->scheduledJob;
    }

    public function setScheduledJob(?ScheduledJob $scheduledJob): self
    {
        $this->scheduledJob = $scheduledJob;
        return $this;
    }
}
// ENTITY RULE
