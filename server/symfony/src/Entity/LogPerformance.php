<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'logPerformance')]
class LogPerformance
{
    #[ORM\Id]
    #[ORM\Column(name: 'id_user_activity', type: 'integer')]
    private int $idUserActivity;

    #[ORM\Column(name: 'log', type: 'text', nullable: true)]
    private ?string $log = null;

    #[ORM\OneToOne(targetEntity: UserActivity::class, inversedBy: 'logPerformance')]
    #[ORM\JoinColumn(name: 'id_user_activity', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?UserActivity $userActivity = null;

    public function getIdUserActivity(): ?int
    {
        return $this->idUserActivity;
    }
    public function setIdUserActivity(int $idUserActivity): self { $this->idUserActivity = $idUserActivity; return $this; }

    public function getLog(): ?string
    {
        return $this->log;
    }

    public function setLog(?string $log): static
    {
        $this->log = $log;

        return $this;
    }

    public function getUserActivity(): ?UserActivity
    {
        return $this->userActivity;
    }

    public function setUserActivity(?UserActivity $userActivity): static
    {
        $this->userActivity = $userActivity;

        return $this;
    }
}
// ENTITY RULE
