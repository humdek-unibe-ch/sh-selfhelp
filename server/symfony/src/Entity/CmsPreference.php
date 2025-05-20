<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'cmsPreferences')]
class CmsPreference
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'callback_api_key', type: 'string', length: 500, nullable: true)]
    private ?string $callbackApiKey = null;

    #[ORM\ManyToOne(targetEntity: Language::class)]
    #[ORM\JoinColumn(name: 'default_language_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?Language $defaultLanguage = null;

    #[ORM\Column(name: 'anonymous_users', type: 'integer', options: ['default' => 0])]
    private int $anonymousUsers = 0;

    #[ORM\Column(name: 'firebase_config', type: 'string', length: 10000, nullable: true)]
    private ?string $firebaseConfig = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCallbackApiKey(): ?string
    {
        return $this->callbackApiKey;
    }

    public function setCallbackApiKey(?string $callbackApiKey): static
    {
        $this->callbackApiKey = $callbackApiKey;

        return $this;
    }

    public function getDefaultLanguage(): ?Language
    {
        return $this->defaultLanguage;
    }

    public function setDefaultLanguage(?Language $language): static
    {
        $this->defaultLanguage = $language;
        return $this;
    }

    public function getAnonymousUsers(): ?int
    {
        return $this->anonymousUsers;
    }

    public function setAnonymousUsers(int $anonymousUsers): static
    {
        $this->anonymousUsers = $anonymousUsers;

        return $this;
    }

    public function getFirebaseConfig(): ?string
    {
        return $this->firebaseConfig;
    }

    public function setFirebaseConfig(?string $firebaseConfig): static
    {
        $this->firebaseConfig = $firebaseConfig;

        return $this;
    }
}
// ENTITY RULE
