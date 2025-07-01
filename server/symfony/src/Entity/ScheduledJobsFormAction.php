<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'scheduledJobs_formActions')]
class ScheduledJobsFormAction
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: ScheduledJob::class)]
    #[ORM\JoinColumn(name: 'id_scheduledJobs', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?ScheduledJob $scheduledJob = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: FormAction::class)]
    #[ORM\JoinColumn(name: 'id_formActions', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?FormAction $formAction = null;

    #[ORM\ManyToOne(targetEntity: DataRow::class)]
    #[ORM\JoinColumn(name: 'id_dataRows', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?DataRow $dataRow = null;

    public function getScheduledJob(): ?ScheduledJob
    {
        return $this->scheduledJob;
    }

    public function setScheduledJob(?ScheduledJob $scheduledJob): self { $this->scheduledJob = $scheduledJob; return $this; }

    public function getFormAction(): ?FormAction
    {
        return $this->formAction;
    }

    public function setFormAction(?FormAction $formAction): self { $this->formAction = $formAction; return $this; }

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
