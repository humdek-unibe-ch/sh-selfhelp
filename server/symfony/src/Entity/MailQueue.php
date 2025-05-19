<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'mailQueue')]
class MailQueue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'from_email', type: 'string', length: 100)]
    private string $fromEmail;

    #[ORM\Column(name: 'from_name', type: 'string', length: 100)]
    private string $fromName;

    #[ORM\Column(name: 'reply_to', type: 'string', length: 100)]
    private string $replyTo;

    #[ORM\Column(name: 'recipient_emails', type: 'text')]
    private string $recipientEmails;

    #[ORM\Column(name: 'cc_emails', type: 'string', length: 1000, nullable: true)]
    private ?string $ccEmails = null;

    #[ORM\Column(name: 'bcc_emails', type: 'string', length: 1000, nullable: true)]
    private ?string $bccEmails = null;

    #[ORM\Column(name: 'subject', type: 'string', length: 1000)]
    private string $subject;

    #[ORM\Column(name: 'body', type: 'text')]
    private string $body;

    #[ORM\Column(name: 'is_html', type: 'boolean')]
    private bool $isHtml = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFromEmail(): ?string
    {
        return $this->fromEmail;
    }

    public function setFromEmail(string $fromEmail): static
    {
        $this->fromEmail = $fromEmail;

        return $this;
    }

    public function getFromName(): ?string
    {
        return $this->fromName;
    }

    public function setFromName(string $fromName): static
    {
        $this->fromName = $fromName;

        return $this;
    }

    public function getReplyTo(): ?string
    {
        return $this->replyTo;
    }

    public function setReplyTo(string $replyTo): static
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    public function getRecipientEmails(): ?string
    {
        return $this->recipientEmails;
    }

    public function setRecipientEmails(string $recipientEmails): static
    {
        $this->recipientEmails = $recipientEmails;

        return $this;
    }

    public function getCcEmails(): ?string
    {
        return $this->ccEmails;
    }

    public function setCcEmails(?string $ccEmails): static
    {
        $this->ccEmails = $ccEmails;

        return $this;
    }

    public function getBccEmails(): ?string
    {
        return $this->bccEmails;
    }

    public function setBccEmails(?string $bccEmails): static
    {
        $this->bccEmails = $bccEmails;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): static
    {
        $this->body = $body;

        return $this;
    }

    public function isHtml(): ?bool
    {
        return $this->isHtml;
    }

    public function setIsHtml(bool $isHtml): static
    {
        $this->isHtml = $isHtml;

        return $this;
    }
}
// ENTITY RULE
