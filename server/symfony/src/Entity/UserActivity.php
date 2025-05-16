<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user_activity')]
class UserActivity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'id_users', type: 'integer')]
    private int $idUsers;

    #[ORM\Column(name: 'url', type: 'string', length: 200)]
    private string $url;

    #[ORM\Column(name: 'timestamp', type: 'datetime')]
    private \DateTimeInterface $timestamp;

    #[ORM\Column(name: 'id_type', type: 'integer')]
    private int $idType;

    #[ORM\Column(name: 'exec_time', type: 'decimal', precision: 10, scale: 8, nullable: true)]
    private ?string $execTime = null;

    #[ORM\Column(name: 'keyword', type: 'string', length: 100, nullable: true)]
    private ?string $keyword = null;

    #[ORM\Column(name: 'params', type: 'string', length: 1000, nullable: true)]
    private ?string $params = null;

    #[ORM\Column(name: 'mobile', type: 'boolean', nullable: true)]
    private ?bool $mobile = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_users', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: ActivityType::class)]
    #[ORM\JoinColumn(name: 'id_type', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?ActivityType $activityType = null;

    #[ORM\OneToOne(mappedBy: 'userActivity', targetEntity: LogPerformance::class)]
    private ?LogPerformance $logPerformance = null;

    public function getId(): ?int { return $this->id; }
    public function getIdUsers(): int { return $this->idUsers; }
    public function setIdUsers(int $idUsers): self { $this->idUsers = $idUsers; return $this; }
    public function getUrl(): string { return $this->url; }
    public function setUrl(string $url): self { $this->url = $url; return $this; }
    public function getTimestamp(): \DateTimeInterface { return $this->timestamp; }
    public function setTimestamp(\DateTimeInterface $timestamp): self { $this->timestamp = $timestamp; return $this; }
    public function getIdType(): int { return $this->idType; }
    public function setIdType(int $idType): self { $this->idType = $idType; return $this; }
    public function getExecTime(): ?string { return $this->execTime; }
    public function setExecTime(?string $execTime): self { $this->execTime = $execTime; return $this; }
    public function getKeyword(): ?string { return $this->keyword; }
    public function setKeyword(?string $keyword): self { $this->keyword = $keyword; return $this; }
    public function getParams(): ?string { return $this->params; }
    public function setParams(?string $params): self { $this->params = $params; return $this; }
    public function isMobile(): ?bool { return $this->mobile; }
    public function setMobile(?bool $mobile): self { $this->mobile = $mobile; return $this; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
    public function getActivityType(): ?ActivityType { return $this->activityType; }
    public function setActivityType(?ActivityType $activityType): self { $this->activityType = $activityType; return $this; }
    public function getLogPerformance(): ?LogPerformance { return $this->logPerformance; }
    public function setLogPerformance(?LogPerformance $logPerformance): self { $this->logPerformance = $logPerformance; return $this; }
}
// ENTITY RULE
