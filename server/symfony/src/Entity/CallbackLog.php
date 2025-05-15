<?php

namespace App\Entity;

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

    public function getId(): ?int { return $this->id; }
    public function getCallbackDate(): ?\DateTimeInterface { return $this->callbackDate; }
    public function setCallbackDate(?\DateTimeInterface $callbackDate): self { $this->callbackDate = $callbackDate; return $this; }
    public function getRemoteAddr(): ?string { return $this->remoteAddr; }
    public function setRemoteAddr(?string $remoteAddr): self { $this->remoteAddr = $remoteAddr; return $this; }
    public function getRedirectUrl(): ?string { return $this->redirectUrl; }
    public function setRedirectUrl(?string $redirectUrl): self { $this->redirectUrl = $redirectUrl; return $this; }
    public function getCallbackParams(): ?string { return $this->callbackParams; }
    public function setCallbackParams(?string $callbackParams): self { $this->callbackParams = $callbackParams; return $this; }
    public function getStatus(): ?string { return $this->status; }
    public function setStatus(?string $status): self { $this->status = $status; return $this; }
    public function getCallbackOutput(): ?string { return $this->callbackOutput; }
    public function setCallbackOutput(?string $callbackOutput): self { $this->callbackOutput = $callbackOutput; return $this; }
}
// ENTITY RULE
