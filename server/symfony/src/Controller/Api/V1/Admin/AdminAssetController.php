<?php

namespace App\Controller\Api\V1\Admin;

use App\Service\CMS\Admin\AdminAssetService;
use App\Service\Core\ApiResponseFormatter;
use App\Service\JSON\JsonSchemaValidationService;
use App\Controller\Trait\RequestValidatorTrait;
use App\Exception\RequestValidationException;
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
    use RequestValidatorTrait;

    public function __construct(
        private readonly AdminAssetService $adminAssetService,
        private readonly ApiResponseFormatter $responseFormatter,
        private readonly JsonSchemaValidationService $jsonSchemaValidationService
    ) {
    }

    /**
     * Get all assets with pagination and search
     * 
     * @route /admin/assets
     * @method GET
     */
    public function getAllAssets(Request $request): JsonResponse
    {
        try {
            $page = max(1, (int) $request->query->get('page', 1));
            $pageSize = max(1, min(1000, (int) $request->query->get('pageSize', 100)));
            $search = $request->query->get('search');
            $folder = $request->query->get('folder');

            $result = $this->adminAssetService->getAllAssets($page, $pageSize, $search, $folder);
            return $this->responseFormatter->formatSuccess($result, 'responses/admin/assets');
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
            return $this->responseFormatter->formatSuccess($asset, 'responses/admin/asset');
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Upload/Create new asset(s) - supports single or multiple files
     * 
     * @route /admin/assets
     * @method POST
     */
    public function createAsset(Request $request): JsonResponse
    {
        try {
            // Validate the form data structure
            $formData = $this->validateAssetFormData($request);
            
            $files = $request->files->get('files', []);
            $singleFile = $request->files->get('file');
            
            // Handle both single file and multiple files
            if ($singleFile) {
                $files = [$singleFile];
            } elseif (!is_array($files)) {
                $files = [$files];
            }

            if (empty($files) || (count($files) === 1 && !$files[0])) {
                return $this->responseFormatter->formatError('At least one file is required', Response::HTTP_BAD_REQUEST);
            }

            $data = [
                'folder' => $formData['folder'] ?? null,
                'file_name' => $formData['file_name'] ?? null,
                'file_names' => $formData['file_names'] ?? []
            ];

            $overwrite = $formData['overwrite'] ?? false;

            if (count($files) === 1) {
                // Single file upload
                $asset = $this->adminAssetService->createAsset($files[0], $data, $overwrite);
                return $this->responseFormatter->formatSuccess(
                    $asset,
                    'responses/admin/asset',
                    Response::HTTP_CREATED
                );
            } else {
                // Multiple file upload
                $result = $this->adminAssetService->createMultipleAssets($files, $data, $overwrite);
                
                $statusCode = $result['failed_uploads'] > 0 ? 
                    Response::HTTP_PARTIAL_CONTENT : 
                    Response::HTTP_CREATED;
                
                return $this->responseFormatter->formatSuccess(
                    $result,
                    'responses/admin/assets_upload',
                    $statusCode
                );
            }
        } catch (RequestValidationException $e) {
            return $this->responseFormatter->formatError(
                'Validation failed',
                Response::HTTP_BAD_REQUEST,
                null,
                $e->getValidationErrors()
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

    /**
     * Validate asset form data against JSON schema
     * 
     * @param Request $request
     * @return array
     * @throws RequestValidationException
     */
    private function validateAssetFormData(Request $request): array
    {
        // Extract form data (non-file fields) for validation
        $formData = [];
        
        // Add optional string fields only if they have values
        if ($request->request->has('folder') && $request->request->get('folder') !== '') {
            $formData['folder'] = $request->request->get('folder');
        }
        
        if ($request->request->has('file_name') && $request->request->get('file_name') !== '') {
            $formData['file_name'] = $request->request->get('file_name');
        }
        
        // Handle file_names array
        $fileNames = $request->request->get('file_names');
        if (is_array($fileNames) && !empty($fileNames)) {
            $formData['file_names'] = array_filter($fileNames, function($name) {
                return !empty($name);
            });
        }
        
        // Always include overwrite as boolean
        $formData['overwrite'] = $request->request->getBoolean('overwrite', false);

        // Add file presence information for validation
        $files = $request->files->get('files', []);
        $singleFile = $request->files->get('file');
        
        if ($singleFile) {
            $formData['file'] = 'binary_file_data'; // Placeholder for schema validation
        } elseif (!empty($files) && is_array($files)) {
            $formData['files'] = array_fill(0, count($files), 'binary_file_data'); // Placeholder array
        }

        // Validate against schema
        try {
            $validationErrors = $this->jsonSchemaValidationService->validate(
                $this->convertToObject($formData), 
                'requests/admin/create_assets'
            );

            if (!empty($validationErrors)) {
                throw new RequestValidationException(
                    $validationErrors,
                    'requests/admin/create_assets',
                    $formData,
                    'Asset form validation failed'
                );
            }
        } catch (\TypeError $e) {
            // Handle schema validation errors more gracefully
            throw new RequestValidationException(
                ['Schema validation error: ' . $e->getMessage()],
                'requests/admin/create_assets',
                $formData,
                'Schema validation failed'
            );
        }

        return $formData;
    }
} 