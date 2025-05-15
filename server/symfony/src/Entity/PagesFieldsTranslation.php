<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'pages_fields_translation')]
class PagesFieldsTranslation
{
    #[ORM\Id]
    #[ORM\Column(name: 'id_pages', type: 'integer')]
    private int $idPages;

    #[ORM\Id]
    #[ORM\Column(name: 'id_fields', type: 'integer')]
    private int $idFields;

    #[ORM\Id]
    #[ORM\Column(name: 'id_languages', type: 'integer')]
    private int $idLanguages;

    #[ORM\Column(name: 'content', type: 'text')]
    private string $content;

    #[ORM\ManyToOne(targetEntity: Page::class)]
    #[ORM\JoinColumn(name: 'id_pages', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Page $page = null;

    #[ORM\ManyToOne(targetEntity: Field::class)]
    #[ORM\JoinColumn(name: 'id_fields', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Field $field = null;

    #[ORM\ManyToOne(targetEntity: Language::class)]
    #[ORM\JoinColumn(name: 'id_languages', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Language $language = null;

    public function getIdPages(): int { return $this->idPages; }
    public function setIdPages(int $idPages): self { $this->idPages = $idPages; return $this; }
    public function getIdFields(): int { return $this->idFields; }
    public function setIdFields(int $idFields): self { $this->idFields = $idFields; return $this; }
    public function getIdLanguages(): int { return $this->idLanguages; }
    public function setIdLanguages(int $idLanguages): self { $this->idLanguages = $idLanguages; return $this; }
    public function getContent(): string { return $this->content; }
    public function setContent(string $content): self { $this->content = $content; return $this; }
    public function getPage(): ?Page { return $this->page; }
    public function setPage(?Page $page): self { $this->page = $page; return $this; }
    public function getField(): ?Field { return $this->field; }
    public function setField(?Field $field): self { $this->field = $field; return $this; }
    public function getLanguage(): ?Language { return $this->language; }
    public function setLanguage(?Language $language): self { $this->language = $language; return $this; }
}
// ENTITY RULE
