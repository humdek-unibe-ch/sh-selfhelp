<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'dataCells')]
class DataCell
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: DataRow::class, inversedBy: 'dataCells', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'id_dataRows', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?DataRow $dataRow = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: DataCol::class, inversedBy: 'dataCells', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'id_dataCols', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?DataCol $dataCol = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Language::class)]
    #[ORM\JoinColumn(name: 'language_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false, options: ['default' => 1])]
    private ?Language $language = null;

    #[ORM\Column(name: 'value', type: 'text')]
    private string $value = '';

    public function getDataRow(): ?DataRow
    {
        return $this->dataRow;
    }
    public function setDataRow(?DataRow $dataRow): static
    {
        $this->dataRow = $dataRow;
        return $this;
    }

    public function getDataCol(): ?DataCol
    {
        return $this->dataCol;
    }
    public function setDataCol(?DataCol $dataCol): static
    {
        $this->dataCol = $dataCol;
        return $this;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }
    public function setLanguage(?Language $language): static
    {
        $this->language = $language;
        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }
}
// ENTITY RULE
