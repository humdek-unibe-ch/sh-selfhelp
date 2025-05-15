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

    #[ORM\Column(name: 'id_mailQueue', type: 'integer')]
    private int $idMailQueue;

    #[ORM\Column(name: 'attachment_name', type: 'string', length: 1000, nullable: true)]
    private ?string $attachmentName = null;

    #[ORM\Column(name: 'attachment_path', type: 'string', length: 1000)]
    private string $attachmentPath;

    #[ORM\Column(name: 'attachment_url', type: 'string', length: 1000)]
    private string $attachmentUrl;

    #[ORM\Column(name: 'template_path', type: 'string', length: 1000)]
    private string $templatePath = '';

    public function getId(): ?int { return $this->id; }
    public function getIdMailQueue(): int { return $this->idMailQueue; }
    public function setIdMailQueue(int $idMailQueue): self { $this->idMailQueue = $idMailQueue; return $this; }
    public function getAttachmentName(): ?string { return $this->attachmentName; }
    public function setAttachmentName(?string $attachmentName): self { $this->attachmentName = $attachmentName; return $this; }
    public function getAttachmentPath(): string { return $this->attachmentPath; }
    public function setAttachmentPath(string $attachmentPath): self { $this->attachmentPath = $attachmentPath; return $this; }
    public function getAttachmentUrl(): string { return $this->attachmentUrl; }
    public function setAttachmentUrl(string $attachmentUrl): self { $this->attachmentUrl = $attachmentUrl; return $this; }
    public function getTemplatePath(): string { return $this->templatePath; }
    public function setTemplatePath(string $templatePath): self { $this->templatePath = $templatePath; return $this; }
}
// ENTITY RULE
