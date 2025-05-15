<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'qualtricsActions')]
class QualtricsAction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'id_qualtricsProjects', type: 'integer')]
    private int $idQualtricsProjects;

    #[ORM\Column(name: 'id_qualtricsSurveys', type: 'integer')]
    private int $idQualtricsSurveys;

    #[ORM\Column(name: 'name', type: 'string', length: 200)]
    private string $name;

    #[ORM\Column(name: 'id_qualtricsProjectActionTriggerTypes', type: 'integer')]
    private int $idQualtricsProjectActionTriggerTypes;

    #[ORM\Column(name: 'id_qualtricsActionScheduleTypes', type: 'integer')]
    private int $idQualtricsActionScheduleTypes;

    #[ORM\Column(name: 'id_qualtricsSurveys_reminder', type: 'integer', nullable: true)]
    private ?int $idQualtricsSurveysReminder = null;

    #[ORM\Column(name: 'schedule_info', type: 'text', nullable: true)]
    private ?string $scheduleInfo = null;

    #[ORM\Column(name: 'id_qualtricsActions', type: 'integer', nullable: true)]
    private ?int $idQualtricsActions = null;

    #[ORM\ManyToOne(targetEntity: QualtricsProject::class)]
    #[ORM\JoinColumn(name: 'id_qualtricsProjects', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?QualtricsProject $qualtricsProject = null;

    #[ORM\ManyToOne(targetEntity: QualtricsSurvey::class)]
    #[ORM\JoinColumn(name: 'id_qualtricsSurveys', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?QualtricsSurvey $qualtricsSurvey = null;

    #[ORM\ManyToOne(targetEntity: Lookup::class)]
    #[ORM\JoinColumn(name: 'id_qualtricsProjectActionTriggerTypes', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Lookup $qualtricsProjectActionTriggerType = null;

    #[ORM\ManyToOne(targetEntity: Lookup::class)]
    #[ORM\JoinColumn(name: 'id_qualtricsActionScheduleTypes', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Lookup $qualtricsActionScheduleType = null;

    #[ORM\ManyToOne(targetEntity: QualtricsSurvey::class)]
    #[ORM\JoinColumn(name: 'id_qualtricsSurveys_reminder', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?QualtricsSurvey $qualtricsSurveyReminder = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(name: 'id_qualtricsActions', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?self $parentQualtricsAction = null;

    public function getId(): ?int { return $this->id; }
    public function getIdQualtricsProjects(): int { return $this->idQualtricsProjects; }
    public function setIdQualtricsProjects(int $idQualtricsProjects): self { $this->idQualtricsProjects = $idQualtricsProjects; return $this; }
    public function getIdQualtricsSurveys(): int { return $this->idQualtricsSurveys; }
    public function setIdQualtricsSurveys(int $idQualtricsSurveys): self { $this->idQualtricsSurveys = $idQualtricsSurveys; return $this; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getIdQualtricsProjectActionTriggerTypes(): int { return $this->idQualtricsProjectActionTriggerTypes; }
    public function setIdQualtricsProjectActionTriggerTypes(int $id): self { $this->idQualtricsProjectActionTriggerTypes = $id; return $this; }
    public function getIdQualtricsActionScheduleTypes(): int { return $this->idQualtricsActionScheduleTypes; }
    public function setIdQualtricsActionScheduleTypes(int $id): self { $this->idQualtricsActionScheduleTypes = $id; return $this; }
    public function getIdQualtricsSurveysReminder(): ?int { return $this->idQualtricsSurveysReminder; }
    public function setIdQualtricsSurveysReminder(?int $id): self { $this->idQualtricsSurveysReminder = $id; return $this; }
    public function getScheduleInfo(): ?string { return $this->scheduleInfo; }
    public function setScheduleInfo(?string $scheduleInfo): self { $this->scheduleInfo = $scheduleInfo; return $this; }
    public function getIdQualtricsActions(): ?int { return $this->idQualtricsActions; }
    public function setIdQualtricsActions(?int $idQualtricsActions): self { $this->idQualtricsActions = $idQualtricsActions; return $this; }
    public function getQualtricsProject(): ?QualtricsProject { return $this->qualtricsProject; }
    public function setQualtricsProject(?QualtricsProject $qualtricsProject): self { $this->qualtricsProject = $qualtricsProject; return $this; }
    public function getQualtricsSurvey(): ?QualtricsSurvey { return $this->qualtricsSurvey; }
    public function setQualtricsSurvey(?QualtricsSurvey $qualtricsSurvey): self { $this->qualtricsSurvey = $qualtricsSurvey; return $this; }
    public function getQualtricsProjectActionTriggerType(): ?Lookup { return $this->qualtricsProjectActionTriggerType; }
    public function setQualtricsProjectActionTriggerType(?Lookup $lookup): self { $this->qualtricsProjectActionTriggerType = $lookup; return $this; }
    public function getQualtricsActionScheduleType(): ?Lookup { return $this->qualtricsActionScheduleType; }
    public function setQualtricsActionScheduleType(?Lookup $lookup): self { $this->qualtricsActionScheduleType = $lookup; return $this; }
    public function getQualtricsSurveyReminder(): ?QualtricsSurvey { return $this->qualtricsSurveyReminder; }
    public function setQualtricsSurveyReminder(?QualtricsSurvey $survey): self { $this->qualtricsSurveyReminder = $survey; return $this; }
    public function getParentQualtricsAction(): ?self { return $this->parentQualtricsAction; }
    public function setParentQualtricsAction(?self $action): self { $this->parentQualtricsAction = $action; return $this; }
}
// ENTITY RULE
