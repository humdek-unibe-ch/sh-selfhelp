<?php

namespace App\Entity;

use App\Repository\RefreshTokensRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RefreshTokensRepository::class)]
class RefreshTokens
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0)]
    private ?string $id_users = null;

    #[ORM\Column(length: 255)]
    private ?string $token_hash = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $expires_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdUsers(): ?string
    {
        return $this->id_users;
    }

    public function setIdUsers(string $id_users): static
    {
        $this->id_users = $id_users;

        return $this;
    }

    public function getTokenHash(): ?string
    {
        return $this->token_hash;
    }

    public function setTokenHash(string $token_hash): static
    {
        $this->token_hash = $token_hash;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expires_at;
    }

    public function setExpiresAt(\DateTimeImmutable $expires_at): static
    {
        $this->expires_at = $expires_at;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
}
