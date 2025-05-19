<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'assets')]
class Asset
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'id_assetTypes', type: 'integer')]
    private int $idAssetTypes;

    #[ORM\Column(name: 'folder', type: 'string', length: 100, nullable: true)]
    private ?string $folder = null;

    #[ORM\Column(name: 'file_name', type: 'string', length: 100, unique: true, nullable: true)]
    private ?string $fileName = null;

    #[ORM\Column(name: 'file_path', type: 'string', length: 1000)]
    private string $filePath;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdAssetTypes(): ?int
    {
        return $this->idAssetTypes;
    }

    public function setIdAssetTypes(int $idAssetTypes): static
    {
        $this->idAssetTypes = $idAssetTypes;

        return $this;
    }

    public function getFolder(): ?string
    {
        return $this->folder;
    }

    public function setFolder(?string $folder): static
    {
        $this->folder = $folder;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): static
    {
        $this->filePath = $filePath;

        return $this;
    }
}
// ENTITY RULE
