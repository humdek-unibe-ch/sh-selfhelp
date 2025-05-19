<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'callbackLogs')]
class CallbackLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'callback_date', type: 'datetime', nullable: true, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeInterface $callbackDate = null;

    #[ORM\Column(name: 'remote_addr', type: 'string', length: 200, nullable: true)]
    private ?string $remoteAddr = null;

    #[ORM\Column(name: 'redirect_url', type: 'string', length: 1000, nullable: true)]
    private ?string $redirectUrl = null;

    #[ORM\Column(name: 'callback_params', type: 'text', nullable: true)]
    private ?string $callbackParams = null;

    #[ORM\Column(name: 'status', type: 'string', length: 200, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(name: 'callback_output', type: 'text', nullable: true)]
    private ?string $callbackOutput = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCallbackDate(): ?\DateTime
    {
        return $this->callbackDate;
    }

    public function setCallbackDate(?\DateTime $callbackDate): static
    {
        $this->callbackDate = $callbackDate;

        return $this;
    }

    public function getRemoteAddr(): ?string
    {
        return $this->remoteAddr;
    }

    public function setRemoteAddr(?string $remoteAddr): static
    {
        $this->remoteAddr = $remoteAddr;

        return $this;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    public function setRedirectUrl(?string $redirectUrl): static
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    public function getCallbackParams(): ?string
    {
        return $this->callbackParams;
    }

    public function setCallbackParams(?string $callbackParams): static
    {
        $this->callbackParams = $callbackParams;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCallbackOutput(): ?string
    {
        return $this->callbackOutput;
    }

    public function setCallbackOutput(?string $callbackOutput): static
    {
        $this->callbackOutput = $callbackOutput;

        return $this;
    }
}
// ENTITY RULE
