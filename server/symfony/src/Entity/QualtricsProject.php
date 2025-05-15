<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'qualtricsProjects')]
class QualtricsProject
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: 'string', length: 200)]
    private string $name;

    #[ORM\Column(name: 'description', type: 'string', length: 1000, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'qualtrics_api', type: 'string', length: 100, nullable: true)]
    private ?string $qualtricsApi = null;

    #[ORM\Column(name: 'api_library_id', type: 'string', length: 100, nullable: true)]
    private ?string $apiLibraryId = null;

    #[ORM\Column(name: 'api_mailing_group_id', type: 'string', length: 100, nullable: true)]
    private ?string $apiMailingGroupId = null;

    #[ORM\Column(name: 'created_on', type: 'datetime')]
    private \DateTimeInterface $createdOn;

    #[ORM\Column(name: 'edited_on', type: 'datetime')]
    private \DateTimeInterface $editedOn;

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }
    public function getQualtricsApi(): ?string { return $this->qualtricsApi; }
    public function setQualtricsApi(?string $qualtricsApi): self { $this->qualtricsApi = $qualtricsApi; return $this; }
    public function getApiLibraryId(): ?string { return $this->apiLibraryId; }
    public function setApiLibraryId(?string $apiLibraryId): self { $this->apiLibraryId = $apiLibraryId; return $this; }
    public function getApiMailingGroupId(): ?string { return $this->apiMailingGroupId; }
    public function setApiMailingGroupId(?string $apiMailingGroupId): self { $this->apiMailingGroupId = $apiMailingGroupId; return $this; }
    public function getCreatedOn(): \DateTimeInterface { return $this->createdOn; }
    public function setCreatedOn(\DateTimeInterface $createdOn): self { $this->createdOn = $createdOn; return $this; }
    public function getEditedOn(): \DateTimeInterface { return $this->editedOn; }
    public function setEditedOn(\DateTimeInterface $editedOn): self { $this->editedOn = $editedOn; return $this; }
}
// ENTITY RULE
