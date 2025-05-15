<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'chatRecipiants')]
class ChatRecipiant
{
    #[ORM\Id]
    #[ORM\Column(name: 'id_users', type: 'integer')]
    private int $idUsers;

    #[ORM\Id]
    #[ORM\Column(name: 'id_chat', type: 'integer')]
    private int $idChat;

    #[ORM\Column(name: 'id_room_users', type: 'integer', nullable: true)]
    private ?int $idRoomUsers = null;

    #[ORM\Column(name: 'is_new', type: 'boolean', options: ['default' => 1])]
    private bool $isNew = true;

    public function getIdUsers(): int { return $this->idUsers; }
    public function setIdUsers(int $idUsers): self { $this->idUsers = $idUsers; return $this; }
    public function getIdChat(): int { return $this->idChat; }
    public function setIdChat(int $idChat): self { $this->idChat = $idChat; return $this; }
    public function getIdRoomUsers(): ?int { return $this->idRoomUsers; }
    public function setIdRoomUsers(?int $idRoomUsers): self { $this->idRoomUsers = $idRoomUsers; return $this; }
    public function getIsNew(): bool { return $this->isNew; }
    public function setIsNew(bool $isNew): self { $this->isNew = $isNew; return $this; }
}
// ENTITY RULE
