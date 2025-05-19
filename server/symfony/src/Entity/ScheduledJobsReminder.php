<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'scheduledJobs_reminders')]
class ScheduledJobsReminder
{
    #[ORM\Id]
    #[ORM\Column(name: 'id_scheduledJobs', type: 'integer')]
    private int $idScheduledJobs;

    #[ORM\Id]
    #[ORM\Column(name: 'id_dataTables', type: 'integer')]
    private int $idDataTables;

    #[ORM\Column(name: 'session_start_date', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $sessionStartDate = null;

    #[ORM\Column(name: 'session_end_date', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $sessionEndDate = null;

    #[ORM\ManyToOne(targetEntity: ScheduledJob::class)]
    #[ORM\JoinColumn(name: 'id_scheduledJobs', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?ScheduledJob $scheduledJob = null;

    #[ORM\ManyToOne(targetEntity: DataTable::class)]
    #[ORM\JoinColumn(name: 'id_dataTables', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?DataTable $dataTable = null;

    public function getIdScheduledJobs(): ?int
    {
        return $this->idScheduledJobs;
    }
    public function setIdScheduledJobs(int $idScheduledJobs): self { $this->idScheduledJobs = $idScheduledJobs; return $this; }

    public function getIdDataTables(): ?int
    {
        return $this->idDataTables;
    }
    public function setIdDataTables(int $idDataTables): self { $this->idDataTables = $idDataTables; return $this; }

    public function getSessionStartDate(): ?\DateTime
    {
        return $this->sessionStartDate;
    }

    public function setSessionStartDate(?\DateTime $sessionStartDate): static
    {
        $this->sessionStartDate = $sessionStartDate;

        return $this;
    }

    public function getSessionEndDate(): ?\DateTime
    {
        return $this->sessionEndDate;
    }

    public function setSessionEndDate(?\DateTime $sessionEndDate): static
    {
        $this->sessionEndDate = $sessionEndDate;

        return $this;
    }

    public function getScheduledJob(): ?ScheduledJob
    {
        return $this->scheduledJob;
    }

    public function setScheduledJob(?ScheduledJob $scheduledJob): static
    {
        $this->scheduledJob = $scheduledJob;

        return $this;
    }

    public function getDataTable(): ?DataTable
    {
        return $this->dataTable;
    }

    public function setDataTable(?DataTable $dataTable): static
    {
        $this->dataTable = $dataTable;

        return $this;
    }
}
// ENTITY RULE
