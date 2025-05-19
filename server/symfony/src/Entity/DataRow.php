<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'dataRows')]
class DataRow
{
    #[ORM\ManyToOne(targetEntity: DataTable::class, inversedBy: 'dataRows')]
    #[ORM\JoinColumn(name: 'id_dataTables', referencedColumnName: 'id', nullable: true)]
    private ?DataTable $dataTable = null;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, DataCell>
     */
    #[ORM\OneToMany(mappedBy: 'dataRow', targetEntity: DataCell::class, cascade: ['persist', 'remove'])]
    private \Doctrine\Common\Collections\Collection $dataCells;

    public function __construct()
    {
        $this->dataCells = new \Doctrine\Common\Collections\ArrayCollection();
    }
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;


    #[ORM\Column(name: 'timestamp', type: 'datetime')]
    private \DateTimeInterface $timestamp;

    #[ORM\Column(name: 'id_users', type: 'integer', nullable: true)]
    private ?int $idUsers = null;

    #[ORM\Column(name: 'id_actionTriggerTypes', type: 'integer', nullable: true)]
    private ?int $idActionTriggerTypes = null;

    public function getId(): ?int
    {
        return $this->id;
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
            $dataCell->setDataRow($this);
        }
        return $this;
    }
    public function removeDataCell(DataCell $dataCell): self
    {
        if ($this->dataCells->removeElement($dataCell)) {
            if ($dataCell->getDataRow() === $this) {
                $dataCell->setDataRow(null);
            }
        }
        return $this;
    }

    public function getTimestamp(): ?\DateTime
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTime $timestamp): static
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getIdUsers(): ?int
    {
        return $this->idUsers;
    }

    public function setIdUsers(?int $idUsers): static
    {
        $this->idUsers = $idUsers;

        return $this;
    }

    public function getIdActionTriggerTypes(): ?int
    {
        return $this->idActionTriggerTypes;
    }

    public function setIdActionTriggerTypes(?int $idActionTriggerTypes): static
    {
        $this->idActionTriggerTypes = $idActionTriggerTypes;

        return $this;
    }
}
// ENTITY RULE
