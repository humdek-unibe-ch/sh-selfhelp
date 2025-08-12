<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'scheduledJobs_actions')]
class ScheduledJobsAction
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: ScheduledJob::class)]
    #[ORM\JoinColumn(name: 'id_scheduledJobs', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?ScheduledJob $scheduledJob = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Action::class)]
    #[ORM\JoinColumn(name: 'id_actions', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Action $action = null;

    #[ORM\ManyToOne(targetEntity: DataRow::class)]
    #[ORM\JoinColumn(name: 'id_dataRows', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?DataRow $dataRow = null;

    public function getScheduledJob(): ?ScheduledJob
    {
        return $this->scheduledJob;
    }

    public function setScheduledJob(?ScheduledJob $scheduledJob): self { $this->scheduledJob = $scheduledJob; return $this; }

    public function getAction(): ?Action
    {
        return $this->action;
    }

    public function setAction(?Action $action): self { $this->action = $action; return $this; }

    public function getDataRow(): ?DataRow
    {
        return $this->dataRow;
    }

    public function setDataRow(?DataRow $dataRow): static
    {
        $this->dataRow = $dataRow;

        return $this;
    }
}
// ENTITY RULE
