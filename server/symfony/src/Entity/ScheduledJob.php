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

    #[ORM\Column(name: 'id_jobTypes', type: 'integer')]
    private int $idJobTypes;

    #[ORM\Column(name: 'id_jobStatus', type: 'integer')]
    private int $idJobStatus;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdJobTypes(): ?int
    {
        return $this->idJobTypes;
    }

    public function setIdJobTypes(int $idJobTypes): static
    {
        $this->idJobTypes = $idJobTypes;

        return $this;
    }

    public function getIdJobStatus(): ?int
    {
        return $this->idJobStatus;
    }

    public function setIdJobStatus(int $idJobStatus): static
    {
        $this->idJobStatus = $idJobStatus;

        return $this;
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
}
// ENTITY RULE
