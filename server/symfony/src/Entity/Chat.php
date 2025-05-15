<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'chat')]
class Chat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'id_snd', type: 'integer')]
    private int $idSnd;

    #[ORM\Column(name: 'id_rcv', type: 'integer', nullable: true)]
    private ?int $idRcv = null;

    #[ORM\Column(name: 'content', type: 'text')]
    private string $content;

    #[ORM\Column(name: 'timestamp', type: 'datetime')]
    private \DateTimeInterface $timestamp;

    #[ORM\Column(name: 'id_rcv_group', type: 'integer')]
    private int $idRcvGroup;

    public function getId(): ?int { return $this->id; }
    public function getIdSnd(): int { return $this->idSnd; }
    public function setIdSnd(int $idSnd): self { $this->idSnd = $idSnd; return $this; }
    public function getIdRcv(): ?int { return $this->idRcv; }
    public function setIdRcv(?int $idRcv): self { $this->idRcv = $idRcv; return $this; }
    public function getContent(): string { return $this->content; }
    public function setContent(string $content): self { $this->content = $content; return $this; }
    public function getTimestamp(): \DateTimeInterface { return $this->timestamp; }
    public function setTimestamp(\DateTimeInterface $timestamp): self { $this->timestamp = $timestamp; return $this; }
    public function getIdRcvGroup(): int { return $this->idRcvGroup; }
    public function setIdRcvGroup(int $idRcvGroup): self { $this->idRcvGroup = $idRcvGroup; return $this; }
}
// ENTITY RULE
