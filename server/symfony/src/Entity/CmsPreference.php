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

    #[ORM\Column(name: 'default_language_id', type: 'integer', nullable: true)]
    private ?int $defaultLanguageId = null;

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

    public function getDefaultLanguageId(): ?int
    {
        return $this->defaultLanguageId;
    }

    public function setDefaultLanguageId(?int $defaultLanguageId): static
    {
        $this->defaultLanguageId = $defaultLanguageId;

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
