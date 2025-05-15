<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'qualtricsSurveys')]
class QualtricsSurvey
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: 'string', length: 200)]
    private string $name;

    #[ORM\Column(name: 'description', type: 'string', length: 1000, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'qualtrics_survey_id', type: 'string', length: 100, nullable: true)]
    private ?string $qualtricsSurveyId = null;

    #[ORM\Column(name: 'id_qualtricsSurveyTypes', type: 'integer')]
    private int $idQualtricsSurveyTypes;

    #[ORM\Column(name: 'participant_variable', type: 'string', length: 100, nullable: true)]
    private ?string $participantVariable = null;

    #[ORM\Column(name: 'group_variable', type: 'integer', options: ['default' => 0])]
    private int $groupVariable = 0;

    #[ORM\Column(name: 'created_on', type: 'datetime')]
    private \DateTimeInterface $createdOn;

    #[ORM\Column(name: 'edited_on', type: 'datetime')]
    private \DateTimeInterface $editedOn;

    #[ORM\Column(name: 'config', type: 'text', nullable: true)]
    private ?string $config = null;

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }
    public function getQualtricsSurveyId(): ?string { return $this->qualtricsSurveyId; }
    public function setQualtricsSurveyId(?string $qualtricsSurveyId): self { $this->qualtricsSurveyId = $qualtricsSurveyId; return $this; }
    public function getIdQualtricsSurveyTypes(): int { return $this->idQualtricsSurveyTypes; }
    public function setIdQualtricsSurveyTypes(int $id): self { $this->idQualtricsSurveyTypes = $id; return $this; }
    public function getParticipantVariable(): ?string { return $this->participantVariable; }
    public function setParticipantVariable(?string $participantVariable): self { $this->participantVariable = $participantVariable; return $this; }
    public function getGroupVariable(): int { return $this->groupVariable; }
    public function setGroupVariable(int $groupVariable): self { $this->groupVariable = $groupVariable; return $this; }
    public function getCreatedOn(): \DateTimeInterface { return $this->createdOn; }
    public function setCreatedOn(\DateTimeInterface $createdOn): self { $this->createdOn = $createdOn; return $this; }
    public function getEditedOn(): \DateTimeInterface { return $this->editedOn; }
    public function setEditedOn(\DateTimeInterface $editedOn): self { $this->editedOn = $editedOn; return $this; }
    public function getConfig(): ?string { return $this->config; }
    public function setConfig(?string $config): self { $this->config = $config; return $this; }
}
// ENTITY RULE
