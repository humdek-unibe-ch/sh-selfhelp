<?php

namespace App\Service\CMS\Admin;

use App\Entity\Asset;
use App\Repository\AssetRepository;
use App\Service\Core\BaseService;
use App\Service\Core\LookupService;
use App\Service\Core\TransactionService;
use App\Exception\ServiceException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class AdminAssetService extends BaseService
{
    private const UPLOAD_DIR = 'uploads/assets/';
    private const ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', // Images
        'pdf', // Documents
        'mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', // Videos
        'css', 'js', // Web files
        'zip', 'rar', '7z', // Archives
        'txt', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx' // Office files
    ];

    public function __construct(
        private readonly AssetRepository $assetRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TransactionService $transactionService,
        private readonly string $projectDir
    ) {
    }

    /**
     * Get all assets
     * 
     * @return array
     */
    public function getAllAssets(): array
    {
        $assets = $this->assetRepository->findAllAssets();
        
        return array_map(function (Asset $asset) {
            return [
                'id' => $asset->getId(),
                'id_asset_types' => $asset->getIdAssetTypes(),
                'folder' => $asset->getFolder(),
                'file_name' => $asset->getFileName(),
                'file_path' => $asset->getFilePath(),
                'url' => '/' . $asset->getFilePath()
            ];
        }, $assets);
    }

    /**
     * Get asset by ID
     * 
     * @param int $id
     * @return array
     */
    public function getAssetById(int $id): array
    {
        $asset = $this->assetRepository->find($id);
        
        if (!$asset) {
            throw new ServiceException('Asset not found', Response::HTTP_NOT_FOUND);
        }

        return [
            'id' => $asset->getId(),
            'id_asset_types' => $asset->getIdAssetTypes(),
            'folder' => $asset->getFolder(),
            'file_name' => $asset->getFileName(),
            'file_path' => $asset->getFilePath(),
            'url' => '/' . $asset->getFilePath()
        ];
    }

    /**
     * Create/Upload new asset
     * 
     * @param UploadedFile $file
     * @param array $data
     * @param bool $overwrite
     * @return array
     */
    public function createAsset(UploadedFile $file, array $data, bool $overwrite = false): array
    {
        $this->entityManager->beginTransaction();
        
        try {
            // Validate file extension
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
                throw new ServiceException(
                    'File type not allowed. Allowed types: ' . implode(', ', self::ALLOWED_EXTENSIONS),
                    Response::HTTP_BAD_REQUEST
                );
            }

            // Determine asset type based on extension
            $assetType = $this->getAssetTypeByExtension($extension);
            
            // Get folder from data or use default
            $folder = $data['folder'] ?? 'general';
            
            // Create filename
            $fileName = $data['file_name'] ?? $file->getClientOriginalName();
            
            // Check if file already exists
            $existingAsset = $this->assetRepository->findByFileName($fileName);
            if ($existingAsset && !$overwrite) {
                throw new ServiceException('File already exists. Use overwrite option to replace it.', Response::HTTP_CONFLICT);
            }

            // Create upload directory if it doesn't exist
            $uploadPath = $this->projectDir . '/public/' . self::UPLOAD_DIR . $folder;
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Move uploaded file
            $filePath = self::UPLOAD_DIR . $folder . '/' . $fileName;
            $file->move($uploadPath, $fileName);

            // Create or update asset entity
            if ($existingAsset && $overwrite) {
                $asset = $existingAsset;
                $transactionType = LookupService::TRANSACTION_TYPES_UPDATE;
                $logMessage = 'Asset updated (overwrite): ' . $fileName;
            } else {
                $asset = new Asset();
                $transactionType = LookupService::TRANSACTION_TYPES_INSERT;
                $logMessage = 'Asset created: ' . $fileName;
            }

            $assetType = $this->entityManager->getRepository(AssetType::class)->find($assetType);
            $asset->setAssetType($assetType);
            $asset->setFolder($folder);
            $asset->setFileName($fileName);
            $asset->setFilePath($filePath);

            $this->entityManager->persist($asset);
            $this->entityManager->flush();

            // Log transaction
            $this->transactionService->logTransaction(
                $transactionType,
                LookupService::TRANSACTION_BY_BY_USER,
                'assets',
                $asset->getId(),
                $asset,
                $logMessage
            );

            $this->entityManager->commit();

            return $this->getAssetById($asset->getId());
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            
            // Clean up uploaded file if it was moved
            if (isset($uploadPath) && isset($fileName)) {
                $fullPath = $uploadPath . '/' . $fileName;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
            
            throw $e;
        }
    }

    /**
     * Delete asset
     * 
     * @param int $id
     * @return bool
     */
    public function deleteAsset(int $id): bool
    {
        $this->entityManager->beginTransaction();
        
        try {
            $asset = $this->assetRepository->find($id);
            
            if (!$asset) {
                throw new ServiceException('Asset not found', Response::HTTP_NOT_FOUND);
            }

            // Store file path for cleanup
            $fullPath = $this->projectDir . '/public/' . $asset->getFilePath();

            // Log transaction before deletion
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_DELETE,
                LookupService::TRANSACTION_BY_BY_USER,
                'assets',
                $asset->getId(),
                $asset,
                'Asset deleted: ' . $asset->getFileName()
            );

            // Delete from database first
            $this->entityManager->remove($asset);
            $this->entityManager->flush();

            // Delete physical file after successful database deletion
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            $this->entityManager->commit();

            return true;
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Get asset type ID based on file extension
     * 
     * @param string $extension
     * @return int
     */
    private function getAssetTypeByExtension(string $extension): int
    {
        return match($extension) {
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg' => 1, // Images
            'pdf', 'txt', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx' => 2, // Documents
            'mp4', 'avi', 'mov', 'wmv', 'flv', 'webm' => 3, // Videos
            'css', 'js' => 4, // Web files
            'zip', 'rar', '7z' => 5, // Archives
            default => 6 // Other
        };
    }
} 