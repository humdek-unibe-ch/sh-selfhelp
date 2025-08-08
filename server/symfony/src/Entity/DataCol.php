<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'dataCols')]
class DataCol
{
    #[ORM\ManyToOne(targetEntity: DataTable::class, inversedBy: 'dataCols', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'id_dataTables', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?DataTable $dataTable = null;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, DataCell>
     */
    #[ORM\OneToMany(mappedBy: 'dataCol', targetEntity: DataCell::class, cascade: ['persist', 'remove'])]
    private \Doctrine\Common\Collections\Collection $dataCells;

    public function __construct()
    {
        $this->dataCells = new \Doctrine\Common\Collections\ArrayCollection();
    }
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private ?string $name = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDataTable(): ?DataTable
    {
        return $this->dataTable;
    }

    public function setDataTable(?DataTable $dataTable): static
    {
        $this->dataTable = $dataTable;
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|DataCell[]
     */
    public function getDataCells(): \Doctrine\Common\Collections\Collection
    {
        return $this->dataCells;
    }

    public function addDataCell(DataCell $dataCell): self
    {
        if (!$this->dataCells->contains($dataCell)) {
            $this->dataCells[] = $dataCell;
            $dataCell->setDataCol($this);
        }
        return $this;
    }

    public function removeDataCell(DataCell $dataCell): self
    {
        if ($this->dataCells->removeElement($dataCell)) {
            if ($dataCell->getDataCol() === $this) {
                $dataCell->setDataCol(null);
            }
        }
        return $this;
    }
}
// ENTITY RULE
