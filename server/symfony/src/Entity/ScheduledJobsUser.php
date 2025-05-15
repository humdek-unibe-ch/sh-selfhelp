<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'scheduledJobs_users')]
class ScheduledJobsUser
{
    #[ORM\Id]
    #[ORM\Column(name: 'id_users', type: 'integer')]
    private int $idUsers;

    #[ORM\Id]
    #[ORM\Column(name: 'id_scheduledJobs', type: 'integer')]
    private int $idScheduledJobs;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_users', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: ScheduledJob::class)]
    #[ORM\JoinColumn(name: 'id_scheduledJobs', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?ScheduledJob $scheduledJob = null;

    public function getIdUsers(): int { return $this->idUsers; }
    public function setIdUsers(int $idUsers): self { $this->idUsers = $idUsers; return $this; }
    public function getIdScheduledJobs(): int { return $this->idScheduledJobs; }
    public function setIdScheduledJobs(int $idScheduledJobs): self { $this->idScheduledJobs = $idScheduledJobs; return $this; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
    public function getScheduledJob(): ?ScheduledJob { return $this->scheduledJob; }
    public function setScheduledJob(?ScheduledJob $scheduledJob): self { $this->scheduledJob = $scheduledJob; return $this; }
}
// ENTITY RULE
