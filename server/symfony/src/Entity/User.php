<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $id_genders = null;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $blocked = false;

    #[ORM\Column(type: 'integer', nullable: true, options: ['default' => 1])]
    private ?int $id_status = 1;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $intern = false;

    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $token = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $id_languages = null;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private bool $is_reminded = true;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $last_login = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $last_url = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true, unique: true)]
    private ?string $user_name = null;

    // Not persisted: for 2FA runtime state
    private bool $twoFactorRequired = false;

    // --- Getters and Setters ---

    public function getId(): ?int { return $this->id; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): self { $this->email = $email; return $this; }
    public function getName(): ?string { return $this->name; }
    public function setName(?string $name): self { $this->name = $name; return $this; }
    public function getPassword(): ?string { return $this->password; }
    public function setPassword(?string $password): self { $this->password = $password; return $this; }
    public function getIdGenders(): ?int { return $this->id_genders; }
    public function setIdGenders(?int $id_genders): self { $this->id_genders = $id_genders; return $this; }
    public function isBlocked(): bool { return $this->blocked; }
    public function setBlocked(bool $blocked): self { $this->blocked = $blocked; return $this; }
    public function getIdStatus(): ?int { return $this->id_status; }
    public function setIdStatus(?int $id_status): self { $this->id_status = $id_status; return $this; }
    public function isIntern(): bool { return $this->intern; }
    public function setIntern(bool $intern): self { $this->intern = $intern; return $this; }
    public function getToken(): ?string { return $this->token; }
    public function setToken(?string $token): self { $this->token = $token; return $this; }
    public function getIdLanguages(): ?int { return $this->id_languages; }
    public function setIdLanguages(?int $id_languages): self { $this->id_languages = $id_languages; return $this; }
    public function isReminded(): bool { return $this->is_reminded; }
    public function setIsReminded(bool $is_reminded): self { $this->is_reminded = $is_reminded; return $this; }
    public function getLastLogin(): ?\DateTimeInterface { return $this->last_login; }
    public function setLastLogin(?\DateTimeInterface $last_login): self { $this->last_login = $last_login; return $this; }
    public function getLastUrl(): ?string { return $this->last_url; }
    public function setLastUrl(?string $last_url): self { $this->last_url = $last_url; return $this; }
    public function getUserName(): ?string { return $this->user_name; }
    public function setUserName(?string $user_name): self { $this->user_name = $user_name; return $this; }

    // 2FA runtime property
    public function isTwoFactorRequired(): bool { return $this->twoFactorRequired; }
    public function setTwoFactorRequired(bool $required): self { $this->twoFactorRequired = $required; return $this; }

    public function getRoles(): array { return ['IS_AUTHENTICATED_FULLY']; }
    public function eraseCredentials(): void { }
    public function getUserIdentifier(): string { return $this->email; }
}
