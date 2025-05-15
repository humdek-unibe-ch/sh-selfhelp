<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'sections_fields_translation')]
class SectionsFieldsTranslation
{
    #[ORM\Id]
    #[ORM\Column(name: 'id_sections', type: 'integer')]
    private int $idSections;

    #[ORM\Id]
    #[ORM\Column(name: 'id_fields', type: 'integer')]
    private int $idFields;

    #[ORM\Id]
    #[ORM\Column(name: 'id_languages', type: 'integer')]
    private int $idLanguages;

    #[ORM\Id]
    #[ORM\Column(name: 'id_genders', type: 'integer')]
    private int $idGenders;

    #[ORM\Column(name: 'content', type: 'text')]
    private string $content;

    #[ORM\Column(name: 'meta', type: 'string', length: 10000, nullable: true)]
    private ?string $meta = null;

    #[ORM\ManyToOne(targetEntity: Section::class)]
    #[ORM\JoinColumn(name: 'id_sections', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Section $section = null;

    #[ORM\ManyToOne(targetEntity: Field::class)]
    #[ORM\JoinColumn(name: 'id_fields', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Field $field = null;

    #[ORM\ManyToOne(targetEntity: Language::class)]
    #[ORM\JoinColumn(name: 'id_languages', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Language $language = null;

    #[ORM\ManyToOne(targetEntity: Gender::class)]
    #[ORM\JoinColumn(name: 'id_genders', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Gender $gender = null;

    public function getIdSections(): int { return $this->idSections; }
    public function setIdSections(int $idSections): self { $this->idSections = $idSections; return $this; }
    public function getIdFields(): int { return $this->idFields; }
    public function setIdFields(int $idFields): self { $this->idFields = $idFields; return $this; }
    public function getIdLanguages(): int { return $this->idLanguages; }
    public function setIdLanguages(int $idLanguages): self { $this->idLanguages = $idLanguages; return $this; }
    public function getIdGenders(): int { return $this->idGenders; }
    public function setIdGenders(int $idGenders): self { $this->idGenders = $idGenders; return $this; }
    public function getContent(): string { return $this->content; }
    public function setContent(string $content): self { $this->content = $content; return $this; }
    public function getMeta(): ?string { return $this->meta; }
    public function setMeta(?string $meta): self { $this->meta = $meta; return $this; }
    public function getSection(): ?Section { return $this->section; }
    public function setSection(?Section $section): self { $this->section = $section; return $this; }
    public function getField(): ?Field { return $this->field; }
    public function setField(?Field $field): self { $this->field = $field; return $this; }
    public function getLanguage(): ?Language { return $this->language; }
    public function setLanguage(?Language $language): self { $this->language = $language; return $this; }
    public function getGender(): ?Gender { return $this->gender; }
    public function setGender(?Gender $gender): self { $this->gender = $gender; return $this; }
}
// ENTITY RULE
