<?php

namespace App\Controller\Api\V1\Admin;

use App\Service\CMS\Admin\AdminAssetService;
use App\Service\Core\ApiResponseFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin Asset Controller
 * 
 * Handles asset management operations for admin interface
 */
class AdminAssetController extends AbstractController
{
    public function __construct(
        private readonly AdminAssetService $adminAssetService,
        private readonly ApiResponseFormatter $responseFormatter
    ) {
    }

    /**
     * Get all assets
     * 
     * @route /admin/assets
     * @method GET
     */
    public function getAllAssets(): JsonResponse
    {
        try {
            $assets = $this->adminAssetService->getAllAssets();
            return $this->responseFormatter->formatSuccess(['assets' => $assets]);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get asset by ID
     * 
     * @route /admin/assets/{assetId}
     * @method GET
     */
    public function getAssetById(int $assetId): JsonResponse
    {
        try {
            $asset = $this->adminAssetService->getAssetById($assetId);
            return $this->responseFormatter->formatSuccess($asset);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Upload/Create new asset
     * 
     * @route /admin/assets
     * @method POST
     */
    public function createAsset(Request $request): JsonResponse
    {
        try {
            $file = $request->files->get('file');
            if (!$file) {
                return $this->responseFormatter->formatError('File is required', Response::HTTP_BAD_REQUEST);
            }

            $data = [
                'folder' => $request->request->get('folder'),
                'file_name' => $request->request->get('file_name')
            ];

            $overwrite = $request->request->getBoolean('overwrite', false);

            $asset = $this->adminAssetService->createAsset($file, $data, $overwrite);
            
            return $this->responseFormatter->formatSuccess(
                $asset,
                null,
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Delete asset
     * 
     * @route /admin/assets/{assetId}
     * @method DELETE
     */
    public function deleteAsset(int $assetId): JsonResponse
    {
        try {
            $this->adminAssetService->deleteAsset($assetId);
            
            return $this->responseFormatter->formatSuccess(['deleted' => true]);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
} 