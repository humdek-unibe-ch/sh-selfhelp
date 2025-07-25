<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'scheduledJobs')]
class ScheduledJob
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'description', type: 'string', length: 1000, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'date_create', type: 'datetime')]
    private \DateTimeInterface $dateCreate;

    #[ORM\Column(name: 'date_to_be_executed', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateToBeExecuted = null;

    #[ORM\Column(name: 'date_executed', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateExecuted = null;

    #[ORM\Column(name: 'config', type: 'string', length: 1000, nullable: true)]
    private ?string $config = null;

    #[ORM\ManyToOne(targetEntity: Lookup::class)]
    #[ORM\JoinColumn(name: 'id_jobStatus', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Lookup $status = null;

    #[ORM\ManyToOne(targetEntity: Lookup::class)]
    #[ORM\JoinColumn(name: 'id_jobTypes', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Lookup $jobType = null;

    #[ORM\OneToMany(targetEntity: ScheduledJobsTask::class, mappedBy: 'scheduledJob', cascade: ['persist', 'remove'])]
    private \Doctrine\Common\Collections\Collection $scheduledJobsTasks;

    #[ORM\OneToMany(targetEntity: ScheduledJobsUser::class, mappedBy: 'scheduledJob', cascade: ['persist', 'remove'])]
    private \Doctrine\Common\Collections\Collection $scheduledJobsUsers;

    #[ORM\OneToMany(targetEntity: ScheduledJobsMailQueue::class, mappedBy: 'scheduledJob', cascade: ['persist', 'remove'])]
    private \Doctrine\Common\Collections\Collection $scheduledJobsMailQueues;

    public function __construct()
    {
        $this->scheduledJobsTasks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->scheduledJobsUsers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->scheduledJobsMailQueues = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDateCreate(): ?\DateTime
    {
        return $this->dateCreate;
    }

    public function setDateCreate(\DateTime $dateCreate): static
    {
        $this->dateCreate = $dateCreate;

        return $this;
    }

    public function getDateToBeExecuted(): ?\DateTime
    {
        return $this->dateToBeExecuted;
    }

    public function setDateToBeExecuted(?\DateTime $dateToBeExecuted): static
    {
        $this->dateToBeExecuted = $dateToBeExecuted;

        return $this;
    }

    public function getDateExecuted(): ?\DateTime
    {
        return $this->dateExecuted;
    }

    public function setDateExecuted(?\DateTime $dateExecuted): static
    {
        $this->dateExecuted = $dateExecuted;

        return $this;
    }

    public function getConfig(): ?string
    {
        return $this->config;
    }

    public function setConfig(?string $config): static
    {
        $this->config = $config;

        return $this;
    }

    public function getStatus(): ?Lookup
    {
        return $this->status;
    }

    public function setStatus(?Lookup $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getJobType(): ?Lookup
    {
        return $this->jobType;
    }

    public function setJobType(?Lookup $jobType): self
    {
        $this->jobType = $jobType;
        return $this;
    }

    public function getScheduledJobsTasks(): \Doctrine\Common\Collections\Collection
    {
        return $this->scheduledJobsTasks;
    }

    public function addScheduledJobsTask(ScheduledJobsTask $scheduledJobsTask): self
    {
        if (!$this->scheduledJobsTasks->contains($scheduledJobsTask)) {
            $this->scheduledJobsTasks->add($scheduledJobsTask);
            $scheduledJobsTask->setScheduledJob($this);
        }
        return $this;
    }

    public function removeScheduledJobsTask(ScheduledJobsTask $scheduledJobsTask): self
    {
        if ($this->scheduledJobsTasks->removeElement($scheduledJobsTask)) {
            if ($scheduledJobsTask->getScheduledJob() === $this) {
                $scheduledJobsTask->setScheduledJob(null);
            }
        }
        return $this;
    }

    public function getScheduledJobsUsers(): \Doctrine\Common\Collections\Collection
    {
        return $this->scheduledJobsUsers;
    }

    public function addScheduledJobsUser(ScheduledJobsUser $scheduledJobsUser): self
    {
        if (!$this->scheduledJobsUsers->contains($scheduledJobsUser)) {
            $this->scheduledJobsUsers->add($scheduledJobsUser);
            $scheduledJobsUser->setScheduledJob($this);
        }
        return $this;
    }

    public function removeScheduledJobsUser(ScheduledJobsUser $scheduledJobsUser): self
    {
        if ($this->scheduledJobsUsers->removeElement($scheduledJobsUser)) {
            if ($scheduledJobsUser->getScheduledJob() === $this) {
                $scheduledJobsUser->setScheduledJob(null);
            }
        }
        return $this;
    }

    public function getScheduledJobsMailQueues(): \Doctrine\Common\Collections\Collection
    {
        return $this->scheduledJobsMailQueues;
    }

    public function addScheduledJobsMailQueue(ScheduledJobsMailQueue $scheduledJobsMailQueue): self
    {
        if (!$this->scheduledJobsMailQueues->contains($scheduledJobsMailQueue)) {
            $this->scheduledJobsMailQueues->add($scheduledJobsMailQueue);
            $scheduledJobsMailQueue->setScheduledJob($this);
        }
        return $this;
    }

    public function removeScheduledJobsMailQueue(ScheduledJobsMailQueue $scheduledJobsMailQueue): self
    {
        if ($this->scheduledJobsMailQueues->removeElement($scheduledJobsMailQueue)) {
            if ($scheduledJobsMailQueue->getScheduledJob() === $this) {
                $scheduledJobsMailQueue->setScheduledJob(null);
            }
        }
        return $this;
    }
}
// ENTITY RULE
