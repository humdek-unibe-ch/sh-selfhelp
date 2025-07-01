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

    private ?Lookup $status = null;
    private ?Lookup $jobType = null;

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
}
// ENTITY RULE
