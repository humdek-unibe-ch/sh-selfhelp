<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'dataCells')]
class DataCell
{
    #[ORM\Id]
    #[ORM\Column(name: 'id_dataRows', type: 'integer')]
    private int $idDataRows;

    #[ORM\Id]
    #[ORM\Column(name: 'id_dataCols', type: 'integer')]
    private int $idDataCols;

    #[ORM\Column(name: 'value', type: 'text')]
    private string $value;

    public function getIdDataRows(): int { return $this->idDataRows; }
    public function setIdDataRows(int $idDataRows): self { $this->idDataRows = $idDataRows; return $this; }
    public function getIdDataCols(): int { return $this->idDataCols; }
    public function setIdDataCols(int $idDataCols): self { $this->idDataCols = $idDataCols; return $this; }
    public function getValue(): string { return $this->value; }
    public function setValue(string $value): self { $this->value = $value; return $this; }
}
// ENTITY RULE
