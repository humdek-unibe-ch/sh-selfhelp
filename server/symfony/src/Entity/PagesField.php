<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'pages_fields')]
class PagesField
{
    #[ORM\Id]
    #[ORM\Column(name: 'id_pages', type: 'integer')]
    private int $idPages;

    #[ORM\Id]
    #[ORM\Column(name: 'id_fields', type: 'integer')]
    private int $idFields;

    #[ORM\Column(name: 'default_value', type: 'string', length: 100, nullable: true)]
    private ?string $defaultValue = null;

    #[ORM\Column(name: 'help', type: 'text', nullable: true)]
    private ?string $help = null;

    #[ORM\ManyToOne(targetEntity: Page::class)]
    #[ORM\JoinColumn(name: 'id_pages', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Page $page = null;

    #[ORM\ManyToOne(targetEntity: Field::class)]
    #[ORM\JoinColumn(name: 'id_fields', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Field $field = null;

    public function getIdPages(): int { return $this->idPages; }
    public function setIdPages(int $idPages): self { $this->idPages = $idPages; return $this; }
    public function getIdFields(): int { return $this->idFields; }
    public function setIdFields(int $idFields): self { $this->idFields = $idFields; return $this; }
    public function getDefaultValue(): ?string { return $this->defaultValue; }
    public function setDefaultValue(?string $defaultValue): self { $this->defaultValue = $defaultValue; return $this; }
    public function getHelp(): ?string { return $this->help; }
    public function setHelp(?string $help): self { $this->help = $help; return $this; }
    public function getPage(): ?Page { return $this->page; }
    public function setPage(?Page $page): self { $this->page = $page; return $this; }
    public function getField(): ?Field { return $this->field; }
    public function setField(?Field $field): self { $this->field = $field; return $this; }
}
// ENTITY RULE
