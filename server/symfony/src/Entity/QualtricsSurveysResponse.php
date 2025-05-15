<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'qualtricsSurveysResponses')]
class QualtricsSurveysResponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'id_users', type: 'integer')]
    private int $idUsers;

    #[ORM\Column(name: 'id_surveys', type: 'integer')]
    private int $idSurveys;

    #[ORM\Column(name: 'id_qualtricsProjectActionTriggerTypes', type: 'integer')]
    private int $idQualtricsProjectActionTriggerTypes;

    #[ORM\Column(name: 'survey_response_id', type: 'string', length: 100, nullable: true)]
    private ?string $surveyResponseId = null;

    #[ORM\Column(name: 'started_on', type: 'datetime')]
    private \DateTimeInterface $startedOn;

    #[ORM\Column(name: 'edited_on', type: 'datetime')]
    private \DateTimeInterface $editedOn;

    public function getId(): ?int { return $this->id; }
    public function getIdUsers(): int { return $this->idUsers; }
    public function setIdUsers(int $idUsers): self { $this->idUsers = $idUsers; return $this; }
    public function getIdSurveys(): int { return $this->idSurveys; }
    public function setIdSurveys(int $idSurveys): self { $this->idSurveys = $idSurveys; return $this; }
    public function getIdQualtricsProjectActionTriggerTypes(): int { return $this->idQualtricsProjectActionTriggerTypes; }
    public function setIdQualtricsProjectActionTriggerTypes(int $id): self { $this->idQualtricsProjectActionTriggerTypes = $id; return $this; }
    public function getSurveyResponseId(): ?string { return $this->surveyResponseId; }
    public function setSurveyResponseId(?string $surveyResponseId): self { $this->surveyResponseId = $surveyResponseId; return $this; }
    public function getStartedOn(): \DateTimeInterface { return $this->startedOn; }
    public function setStartedOn(\DateTimeInterface $startedOn): self { $this->startedOn = $startedOn; return $this; }
    public function getEditedOn(): \DateTimeInterface { return $this->editedOn; }
    public function setEditedOn(\DateTimeInterface $editedOn): self { $this->editedOn = $editedOn; return $this; }
}
// ENTITY RULE
