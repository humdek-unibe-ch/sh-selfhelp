<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'chatRoom_users')]
class ChatRoomUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'id_users', type: 'integer')]
    private int $idUsers;

    #[ORM\Column(name: 'id_chatRoom', type: 'integer')]
    private int $idChatRoom;

    #[ORM\Column(name: 'is_admin', type: 'boolean')]
    private bool $isAdmin = false;

    #[ORM\Column(name: 'joined_at', type: 'datetime')]
    private \DateTimeInterface $joinedAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_users', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: ChatRoom::class)]
    #[ORM\JoinColumn(name: 'id_chatRoom', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?ChatRoom $chatRoom = null;

    public function getId(): ?int { return $this->id; }
    public function getIdUsers(): int { return $this->idUsers; }
    public function setIdUsers(int $idUsers): self { $this->idUsers = $idUsers; return $this; }
    public function getIdChatRoom(): int { return $this->idChatRoom; }
    public function setIdChatRoom(int $idChatRoom): self { $this->idChatRoom = $idChatRoom; return $this; }
    public function isIsAdmin(): bool { return $this->isAdmin; }
    public function setIsAdmin(bool $isAdmin): self { $this->isAdmin = $isAdmin; return $this; }
    public function getJoinedAt(): \DateTimeInterface { return $this->joinedAt; }
    public function setJoinedAt(\DateTimeInterface $joinedAt): self { $this->joinedAt = $joinedAt; return $this; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
    public function getChatRoom(): ?ChatRoom { return $this->chatRoom; }
    public function setChatRoom(?ChatRoom $chatRoom): self { $this->chatRoom = $chatRoom; return $this; }

    public function isAdmin(): ?bool
    {
        return $this->isAdmin;
    }
}
// ENTITY RULE
