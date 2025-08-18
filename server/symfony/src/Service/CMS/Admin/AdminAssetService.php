<?php

namespace App\Service\CMS\Admin;

use App\Entity\Asset;
use App\Repository\AssetRepository;
use App\Service\Core\BaseService;
use App\Service\Core\LookupService;
use App\Service\Core\TransactionService;
use App\Service\Core\CacheableServiceTrait;
use App\Service\Cache\Core\CacheService;
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
        'json', // JSON files
        'txt', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx' // Office files
    ];

    public function __construct(
        private readonly AssetRepository $assetRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TransactionService $transactionService,
        private readonly LookupService $lookupService,
        private readonly string $projectDir
    ) {
    }

    /**
     * Get all assets with pagination and search
     * 
     * @param int $page
     * @param int $pageSize
     * @param string|null $search
     * @param string|null $folder
     * @return array
     */
    public function getAllAssets(int $page = 1, int $pageSize = 100, ?string $search = null, ?string $folder = null): array
    {
        // Create cache key based on parameters
        $cacheKey = "assets_list_{$page}_{$pageSize}_" . md5(($search ?? '') . ($folder ?? ''));
        
        return $this->getCache(
            CacheService::CATEGORY_ASSETS,
            $cacheKey,
            function() use ($page, $pageSize, $search, $folder) {
                return $this->fetchAssetsFromDatabase($page, $pageSize, $search, $folder);
            },
null
        );
    }
    
    private function fetchAssetsFromDatabase(int $page, int $pageSize, ?string $search, ?string $folder): array
    {
        $result = $this->assetRepository->findAssetsWithPagination($page, $pageSize, $search, $folder);
        
        $assets = array_map(function (Asset $asset) {
            return [
                'id' => $asset->getId(),
                'asset_type' => $asset->getAssetType()->getLookupValue(),
                'folder' => $asset->getFolder(),
                'file_name' => $asset->getFileName(),
                'file_path' => $asset->getFilePath(),
                'url' => '/' . $asset->getFilePath()
            ];
        }, $result['assets']);

        return [
            'assets' => $assets,
            'pagination' => [
                'page' => $page,
                'pageSize' => $pageSize,
                'total' => $result['total'],
                'totalPages' => ceil($result['total'] / $pageSize)
            ]
        ];
    }

    /**
     * Get asset by ID
     * 
     * @param int $id
     * @return array
     */
    public function getAssetById(int $id): array
    {
        $asset = $this->assetRepository->findOneBy(['id' => $id]);
        
        if (!$asset) {
            throw new ServiceException('Asset not found', Response::HTTP_NOT_FOUND);
        }

        return [
            'id' => $asset->getId(),
            'asset_type' => $asset->getAssetType()->getLookupValue(),
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
            // Validate file is properly uploaded
            if (!$file->isValid()) {
                throw new ServiceException('File upload failed: ' . $file->getErrorMessage(), Response::HTTP_BAD_REQUEST);
            }

            // Validate file extension
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
                throw new ServiceException(
                    'File type not allowed. Allowed types: ' . implode(', ', self::ALLOWED_EXTENSIONS),
                    Response::HTTP_BAD_REQUEST
                );
            }

            // Determine asset type based on extension
            $assetType = $this->lookupService->findByTypeAndCode(
                LookupService::ASSET_TYPES,
                in_array($extension, ['css']) ? LookupService::ASSET_TYPES_CSS : LookupService::ASSET_TYPES_ASSET
            );
            
            // Get folder from data or use default
            $folder = $data['folder'] ?? 'general';
            
            // Create filename - preserve original name if no custom name provided
            $fileName = !empty($data['file_name']) ? $data['file_name'] : $file->getClientOriginalName();
            
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

            // Move uploaded file FIRST to avoid file size errors on temp files
            $filePath = self::UPLOAD_DIR . $folder . '/' . $fileName;
            $fullUploadPath = $uploadPath . '/' . $fileName;
            
            // Move the file immediately
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
            if (isset($fullUploadPath) && file_exists($fullUploadPath)) {
                unlink($fullUploadPath);
            }
            
            throw $e;
        }
    }

    /**
     * Create/Upload multiple assets
     * 
     * @param array $files UploadedFile[]
     * @param array $data
     * @param bool $overwrite
     * @return array
     */
    public function createMultipleAssets(array $files, array $data, bool $overwrite = false): array
    {
        $results = [];
        $errors = [];
        
        foreach ($files as $index => $file) {
            try {
                $fileData = $data;
                // Allow individual file names if provided
                if (isset($data['file_names'][$index])) {
                    $fileData['file_name'] = $data['file_names'][$index];
                }
                
                $result = $this->createAsset($file, $fileData, $overwrite);
                $results[] = $result;
            } catch (\Exception $e) {
                $errors[] = [
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'uploaded' => $results,
            'errors' => $errors,
            'total_files' => count($files),
            'successful_uploads' => count($results),
            'failed_uploads' => count($errors)
        ];
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
} 