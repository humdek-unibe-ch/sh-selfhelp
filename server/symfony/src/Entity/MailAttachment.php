<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'mailAttachments')]
class MailAttachment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: MailQueue::class)]
    #[ORM\JoinColumn(name: 'id_mailQueue', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?MailQueue $mailQueue = null;

    #[ORM\Column(name: 'attachment_name', type: 'string', length: 1000, nullable: true)]
    private ?string $attachmentName = null;

    #[ORM\Column(name: 'attachment_path', type: 'string', length: 1000)]
    private string $attachmentPath;

    #[ORM\Column(name: 'attachment_url', type: 'string', length: 1000)]
    private string $attachmentUrl;

    #[ORM\Column(name: 'template_path', type: 'string', length: 1000)]
    private string $templatePath = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMailQueue(): ?MailQueue
    {
        return $this->mailQueue;
    }

    public function setMailQueue(MailQueue $mailQueue): static
    {
        $this->mailQueue = $mailQueue;

        return $this;
    }

    public function getAttachmentName(): ?string
    {
        return $this->attachmentName;
    }

    public function setAttachmentName(?string $attachmentName): static
    {
        $this->attachmentName = $attachmentName;

        return $this;
    }

    public function getAttachmentPath(): ?string
    {
        return $this->attachmentPath;
    }

    public function setAttachmentPath(string $attachmentPath): static
    {
        $this->attachmentPath = $attachmentPath;

        return $this;
    }

    public function getAttachmentUrl(): ?string
    {
        return $this->attachmentUrl;
    }

    public function setAttachmentUrl(string $attachmentUrl): static
    {
        $this->attachmentUrl = $attachmentUrl;

        return $this;
    }

    public function getTemplatePath(): ?string
    {
        return $this->templatePath;
    }

    public function setTemplatePath(string $templatePath): static
    {
        $this->templatePath = $templatePath;

        return $this;
    }
}
// ENTITY RULE
