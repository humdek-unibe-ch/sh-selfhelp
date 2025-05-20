<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface
{
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UsersGroup::class, orphanRemoval: true)]
    private \Doctrine\Common\Collections\Collection $usersGroups;

    // --- RELATIONSHIPS ---

    /**
     * @return \Doctrine\Common\Collections\Collection|Group[]
     */
    public function getGroups(): \Doctrine\Common\Collections\Collection
    {
        return new \Doctrine\Common\Collections\ArrayCollection(
            array_map(fn($ug) => $ug->getGroup(), $this->usersGroups->toArray())
        );
    }

    public function addUsersGroup(UsersGroup $usersGroup): self
    {
        if (!$this->usersGroups->contains($usersGroup)) {
            $this->usersGroups[] = $usersGroup;
            $usersGroup->setUser($this);
        }
        return $this;
    }

    public function removeUsersGroup(UsersGroup $usersGroup): self
    {
        if ($this->usersGroups->removeElement($usersGroup)) {
            if ($usersGroup->getUser() === $this) {
                $usersGroup->setUser(null);
            }
        }
        return $this;
    }

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserActivity::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private \Doctrine\Common\Collections\Collection $userActivities;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Transaction::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private \Doctrine\Common\Collections\Collection $transactions;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: RefreshToken::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private \Doctrine\Common\Collections\Collection $refreshTokens;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ScheduledJobsUser::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private \Doctrine\Common\Collections\Collection $scheduledJobsUsers;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ValidationCode::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private \Doctrine\Common\Collections\Collection $validationCodes;

    public function __construct()
    {
        $this->usersGroups = new \Doctrine\Common\Collections\ArrayCollection();
        $this->userActivities = new \Doctrine\Common\Collections\ArrayCollection();
        $this->transactions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->refreshTokens = new \Doctrine\Common\Collections\ArrayCollection();
        $this->scheduledJobsUsers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->validationCodes = new \Doctrine\Common\Collections\ArrayCollection();
        
        // Set default userType in service layer or controller when creating new users
        // The default value should be the 'user' type from lookups table
        // This cannot be set directly in the entity as it requires database access
    }

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

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $device_id = null;

    #[ORM\Column(type: 'string', length: 200, nullable: true)]
    private ?string $device_token = null;

    #[ORM\Column(type: 'string', length: 1000, nullable: true)]
    private ?string $security_questions = null;

    #[ORM\ManyToOne(targetEntity: Lookup::class)]
    #[ORM\JoinColumn(name: 'id_userTypes', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE', options: ['default' => 72])] //TODO: set default value to user type dynamically
    private ?Lookup $userType = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true, unique: true)]
    private ?string $user_name = null;

    // Not persisted: for 2FA runtime state
    // This property is used for 2FA runtime state and is not stored in the database
    private bool $twoFactorRequired = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getUserType(): ?Lookup
    {
        return $this->userType;
    }

    public function setUserType(?Lookup $userType): self
    {
        $this->userType = $userType;
        return $this;
    }

    public function getIdGenders(): ?int
    {
        return $this->id_genders;
    }

    public function setIdGenders(?int $id_genders): static
    {
        $this->id_genders = $id_genders;

        return $this;
    }

    public function isBlocked(): ?bool
    {
        return $this->blocked;
    }

    public function setBlocked(bool $blocked): static
    {
        $this->blocked = $blocked;

        return $this;
    }

    public function getIdStatus(): ?int
    {
        return $this->id_status;
    }

    public function setIdStatus(?int $id_status): static
    {
        $this->id_status = $id_status;

        return $this;
    }

    public function isIntern(): ?bool
    {
        return $this->intern;
    }

    public function setIntern(bool $intern): static
    {
        $this->intern = $intern;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getIdLanguages(): ?int
    {
        return $this->id_languages;
    }

    public function setIdLanguages(?int $id_languages): static
    {
        $this->id_languages = $id_languages;

        return $this;
    }

    public function isReminded(): ?bool
    {
        return $this->is_reminded;
    }

    public function setIsReminded(bool $is_reminded): static
    {
        $this->is_reminded = $is_reminded;

        return $this;
    }

    public function getLastLogin(): ?\DateTime
    {
        return $this->last_login;
    }

    public function setLastLogin(?\DateTime $last_login): static
    {
        $this->last_login = $last_login;

        return $this;
    }

    public function getLastUrl(): ?string
    {
        return $this->last_url;
    }

    public function setLastUrl(?string $last_url): static
    {
        $this->last_url = $last_url;

        return $this;
    }


    public function getRoles(): array { return ['IS_AUTHENTICATED_FULLY']; }
    public function eraseCredentials(): void { }
    public function getUserIdentifier(): string { return $this->email; }

    // --- RELATIONSHIP GETTERS & SETTERS ---
    /**
     * @return \Doctrine\Common\Collections\Collection|UserActivity[]
     */
    public function getUserActivities(): \Doctrine\Common\Collections\Collection
    {
        return $this->userActivities;
    }
    public function addUserActivity(UserActivity $userActivity): self
    {
        if (!$this->userActivities->contains($userActivity)) {
            $this->userActivities[] = $userActivity;
            $userActivity->setUser($this);
        }
        return $this;
    }
    public function removeUserActivity(UserActivity $userActivity): self
    {
        if ($this->userActivities->removeElement($userActivity)) {
            if ($userActivity->getUser() === $this) {
                $userActivity->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|Transaction[]
     */
    public function getTransactions(): \Doctrine\Common\Collections\Collection
    {
        return $this->transactions;
    }
    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setUser($this);
        }
        return $this;
    }
    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->removeElement($transaction)) {
            if ($transaction->getUser() === $this) {
                $transaction->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|RefreshToken[]
     */
    public function getRefreshTokens(): \Doctrine\Common\Collections\Collection
    {
        return $this->refreshTokens;
    }
    public function addRefreshToken(RefreshToken $refreshToken): self
    {
        if (!$this->refreshTokens->contains($refreshToken)) {
            $this->refreshTokens[] = $refreshToken;
            $refreshToken->setUser($this);
        }
        return $this;
    }
    public function removeRefreshToken(RefreshToken $refreshToken): self
    {
        if ($this->refreshTokens->removeElement($refreshToken)) {
            if ($refreshToken->getUser() === $this) {
                $refreshToken->setUser(null);
            }
        }
        return $this;
    }


    /**
     * @return \Doctrine\Common\Collections\Collection|ScheduledJobsUser[]
     */
    public function getScheduledJobsUsers(): \Doctrine\Common\Collections\Collection
    {
        return $this->scheduledJobsUsers;
    }
    public function addScheduledJobsUser(ScheduledJobsUser $scheduledJobsUser): self
    {
        if (!$this->scheduledJobsUsers->contains($scheduledJobsUser)) {
            $this->scheduledJobsUsers[] = $scheduledJobsUser;
            $scheduledJobsUser->setUser($this);
        }
        return $this;
    }
    public function removeScheduledJobsUser(ScheduledJobsUser $scheduledJobsUser): self
    {
        if ($this->scheduledJobsUsers->removeElement($scheduledJobsUser)) {
            if ($scheduledJobsUser->getUser() === $this) {
                $scheduledJobsUser->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|ValidationCode[]
     */
    public function getValidationCodes(): \Doctrine\Common\Collections\Collection
    {
        return $this->validationCodes;
    }
    public function addValidationCode(ValidationCode $validationCode): self
    {
        if (!$this->validationCodes->contains($validationCode)) {
            $this->validationCodes[] = $validationCode;
            $validationCode->setUser($this);
        }
        return $this;
    }
    public function removeValidationCode(ValidationCode $validationCode): self
    {
        if ($this->validationCodes->removeElement($validationCode)) {
            if ($validationCode->getUser() === $this) {
                $validationCode->setUser(null);
            }
        }
        return $this;
    }

    /**
     * Check if two-factor authentication is required for this user
     * This is determined by checking if any of the user's groups require 2FA
     * 
     * @return bool True if 2FA is required, false otherwise
     */
    public function isTwoFactorRequired(): bool 
    { 
        // First check if it's already set (for performance)
        if ($this->twoFactorRequired) {
            return true;
        }
        
        // Check if any of the user's groups require 2FA
        foreach ($this->usersGroups as $userGroup) {
            $group = $userGroup->getGroup();
            if ($group && $group->isRequires2fa()) {
                $this->twoFactorRequired = true;
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Set the two-factor authentication requirement flag
     * 
     * @param bool $required Whether 2FA is required
     * @return self
     */
    public function setTwoFactorRequired(bool $required): self 
    { 
        $this->twoFactorRequired = $required; 
        return $this; 
    }
    
}
// ENTITY RULE
