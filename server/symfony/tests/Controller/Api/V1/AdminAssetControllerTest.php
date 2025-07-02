<?php

namespace App\Tests\Controller\Api\V1;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class AdminAssetControllerTest extends BaseControllerTest
{
    private array $createdAssetIds = [];

    /**
     * @group asset-management
     */
    public function testGetAllAssetsSuccess(): void
    {
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/assets',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        
        // Validate response structure with pagination
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('assets', $data['data']);
        $this->assertArrayHasKey('pagination', $data['data']);
        
        // Validate pagination structure
        $pagination = $data['data']['pagination'];
        $this->assertArrayHasKey('page', $pagination);
        $this->assertArrayHasKey('pageSize', $pagination);
        $this->assertArrayHasKey('total', $pagination);
        $this->assertArrayHasKey('totalPages', $pagination);
        $this->assertIsInt($pagination['page']);
        $this->assertIsInt($pagination['pageSize']);
        $this->assertIsInt($pagination['total']);
        
        // Validate assets array
        $this->assertIsArray($data['data']['assets']);
        
        if (!empty($data['data']['assets'])) {
            $asset = $data['data']['assets'][0];
            $this->assertArrayHasKey('id', $asset);
            $this->assertArrayHasKey('asset_type', $asset);
            $this->assertArrayHasKey('folder', $asset);
            $this->assertArrayHasKey('file_name', $asset);
            $this->assertArrayHasKey('file_path', $asset);
            $this->assertArrayHasKey('url', $asset);
            $this->assertIsInt($asset['id']);
            $this->assertIsString($asset['asset_type']);
        }
    }

    /**
     * @group asset-management
     */
    public function testGetAllAssetsWithPaginationAndSearch(): void
    {
        // Test with pagination parameters
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/assets?page=1&pageSize=5&search=test&folder=test',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        
        // Validate pagination reflects requested parameters
        $pagination = $data['data']['pagination'];
        $this->assertSame(1, $pagination['page']);
        $this->assertSame(5, $pagination['pageSize']);
        
        // Validate assets array size doesn't exceed pageSize
        $this->assertLessThanOrEqual(5, count($data['data']['assets']));
    }

    /**
     * @group asset-management
     */
    public function testCreateAssetSuccess(): void
    {
        // Create a temporary test file
        $testFilePath = tempnam(sys_get_temp_dir(), 'test_image');
        file_put_contents($testFilePath, 'test image content');
        
        $uploadedFile = new UploadedFile(
            $testFilePath,
            'test-image.jpg',
            'image/jpeg',
            null,
            true
        );

        $this->client->request(
            'POST',
            '/cms-api/v1/admin/assets',
            [
                'folder' => 'test',
                'file_name' => 'test-upload.jpg'
            ],
            ['file' => $uploadedFile],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()
            ]
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        
        // Validate response structure
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('id', $data['data']);
        $this->assertArrayHasKey('file_name', $data['data']);
        $this->assertArrayHasKey('folder', $data['data']);
        $this->assertArrayHasKey('url', $data['data']);
        
        // Validate data
        $this->assertSame('test-upload.jpg', $data['data']['file_name']);
        $this->assertSame('test', $data['data']['folder']);
        $this->assertStringContains('uploads/assets/test/test-upload.jpg', $data['data']['file_path']);
        
        // Store for cleanup
        $this->createdAssetIds[] = $data['data']['id'];
    }

    /**
     * @group asset-management
     */
    public function testCreateAssetWithOverwrite(): void
    {
        // Create first asset
        $testFilePath1 = tempnam(sys_get_temp_dir(), 'test_image1');
        file_put_contents($testFilePath1, 'test image content 1');
        
        $uploadedFile1 = new UploadedFile(
            $testFilePath1,
            'overwrite-test.jpg',
            'image/jpeg',
            null,
            true
        );

        $this->client->request(
            'POST',
            '/cms-api/v1/admin/assets',
            [
                'folder' => 'test',
                'file_name' => 'overwrite-test.jpg'
            ],
            ['file' => $uploadedFile1],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()
            ]
        );

        $firstResponse = $this->client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $firstResponse->getStatusCode());
        
        $firstData = json_decode($firstResponse->getContent(), true);
        $this->createdAssetIds[] = $firstData['data']['id'];

        // Try to create second asset with same name (should fail without overwrite)
        $testFilePath2 = tempnam(sys_get_temp_dir(), 'test_image2');
        file_put_contents($testFilePath2, 'test image content 2');
        
        $uploadedFile2 = new UploadedFile(
            $testFilePath2,
            'overwrite-test.jpg',
            'image/jpeg',
            null,
            true
        );

        $this->client->request(
            'POST',
            '/cms-api/v1/admin/assets',
            [
                'folder' => 'test',
                'file_name' => 'overwrite-test.jpg'
            ],
            ['file' => $uploadedFile2],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()
            ]
        );

        $conflictResponse = $this->client->getResponse();
        $this->assertSame(Response::HTTP_CONFLICT, $conflictResponse->getStatusCode());

        // Now try with overwrite flag
        $testFilePath3 = tempnam(sys_get_temp_dir(), 'test_image3');
        file_put_contents($testFilePath3, 'test image content 3');
        
        $uploadedFile3 = new UploadedFile(
            $testFilePath3,
            'overwrite-test.jpg',
            'image/jpeg',
            null,
            true
        );

        $this->client->request(
            'POST',
            '/cms-api/v1/admin/assets',
            [
                'folder' => 'test',
                'file_name' => 'overwrite-test.jpg',
                'overwrite' => '1'
            ],
            ['file' => $uploadedFile3],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()
            ]
        );

        $overwriteResponse = $this->client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $overwriteResponse->getStatusCode());
    }

    /**
     * @group asset-management
     */
    public function testCreateAssetInvalidFileType(): void
    {
        // Create a temporary test file with invalid extension
        $testFilePath = tempnam(sys_get_temp_dir(), 'test_invalid');
        file_put_contents($testFilePath, 'invalid file content');
        
        $uploadedFile = new UploadedFile(
            $testFilePath,
            'test-invalid.exe',
            'application/octet-stream',
            null,
            true
        );

        $this->client->request(
            'POST',
            '/cms-api/v1/admin/assets',
            [
                'folder' => 'test',
                'file_name' => 'test-invalid.exe'
            ],
            ['file' => $uploadedFile],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()
            ]
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * @group asset-management
     */
    public function testCreateAssetMissingFile(): void
    {
        $this->client->request(
            'POST',
            '/cms-api/v1/admin/assets',
            [
                'folder' => 'test'
            ],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()
            ]
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * @group asset-management
     */
    public function testGetAssetByIdSuccess(): void
    {
        // First create an asset
        $testFilePath = tempnam(sys_get_temp_dir(), 'test_get_by_id');
        file_put_contents($testFilePath, 'test content for get by id');
        
        $uploadedFile = new UploadedFile(
            $testFilePath,
            'get-by-id-test.txt',
            'text/plain',
            null,
            true
        );

        $this->client->request(
            'POST',
            '/cms-api/v1/admin/assets',
            [
                'folder' => 'test',
                'file_name' => 'get-by-id-test.txt'
            ],
            ['file' => $uploadedFile],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()
            ]
        );

        $createResponse = $this->client->getResponse();
        $createData = json_decode($createResponse->getContent(), true);
        $assetId = $createData['data']['id'];
        $this->createdAssetIds[] = $assetId;

        // Now get the asset by ID
        $this->client->request(
            'GET',
            "/cms-api/v1/admin/assets/{$assetId}",
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        
        // Validate response structure
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertSame($assetId, $data['data']['id']);
        $this->assertSame('get-by-id-test.txt', $data['data']['file_name']);
        $this->assertSame('test', $data['data']['folder']);
    }

    /**
     * @group asset-management
     */
    public function testGetAssetByIdNotFound(): void
    {
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/assets/99999',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * @group asset-management
     */
    public function testDeleteAssetSuccess(): void
    {
        // First create an asset
        $testFilePath = tempnam(sys_get_temp_dir(), 'test_delete');
        file_put_contents($testFilePath, 'test content for delete');
        
        $uploadedFile = new UploadedFile(
            $testFilePath,
            'delete-test.txt',
            'text/plain',
            null,
            true
        );

        $this->client->request(
            'POST',
            '/cms-api/v1/admin/assets',
            [
                'folder' => 'test',
                'file_name' => 'delete-test.txt'
            ],
            ['file' => $uploadedFile],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()
            ]
        );

        $createResponse = $this->client->getResponse();
        $createData = json_decode($createResponse->getContent(), true);
        $assetId = $createData['data']['id'];

        // Now delete the asset
        $this->client->request(
            'DELETE',
            "/cms-api/v1/admin/assets/{$assetId}",
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertTrue($data['data']['deleted']);

        // Verify asset is deleted
        $this->client->request(
            'GET',
            "/cms-api/v1/admin/assets/{$assetId}",
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $getResponse = $this->client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $getResponse->getStatusCode());
    }

    /**
     * @group asset-management
     */
    public function testDeleteAssetNotFound(): void
    {
        $this->client->request(
            'DELETE',
            '/cms-api/v1/admin/assets/99999',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * @group asset-management
     */
    public function testUnauthorizedAccess(): void
    {
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/assets',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    /**
     * @group asset-management
     */
    public function testCreateMultipleAssetsSuccess(): void
    {
        // Create multiple temporary test files
        $testFiles = [];
        for ($i = 1; $i <= 3; $i++) {
            $testFilePath = tempnam(sys_get_temp_dir(), "test_image_$i");
            file_put_contents($testFilePath, "test image content $i");
            
            $testFiles[] = new UploadedFile(
                $testFilePath,
                "test-multi-$i.jpg",
                'image/jpeg',
                null,
                true
            );
        }

        $this->client->request(
            'POST',
            '/cms-api/v1/admin/assets',
            [
                'folder' => 'test-multi',
                'file_names' => ['custom-1.jpg', 'custom-2.jpg', 'custom-3.jpg']
            ],
            ['files' => $testFiles],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken()
            ]
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        
        // Validate multiple upload response structure
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('uploaded', $data['data']);
        $this->assertArrayHasKey('errors', $data['data']);
        $this->assertArrayHasKey('total_files', $data['data']);
        $this->assertArrayHasKey('successful_uploads', $data['data']);
        $this->assertArrayHasKey('failed_uploads', $data['data']);
        
        // Validate upload statistics
        $this->assertSame(3, $data['data']['total_files']);
        $this->assertSame(3, $data['data']['successful_uploads']);
        $this->assertSame(0, $data['data']['failed_uploads']);
        $this->assertEmpty($data['data']['errors']);
        
        // Validate uploaded assets
        $this->assertCount(3, $data['data']['uploaded']);
        
        foreach ($data['data']['uploaded'] as $index => $asset) {
            $this->assertArrayHasKey('id', $asset);
            $this->assertArrayHasKey('file_name', $asset);
            $this->assertArrayHasKey('folder', $asset);
            $this->assertSame('test-multi', $asset['folder']);
            $this->assertSame("custom-" . ($index + 1) . ".jpg", $asset['file_name']);
            
            // Store for cleanup
            $this->createdAssetIds[] = $asset['id'];
        }
    }

    protected function tearDown(): void
    {
        // Clean up created assets
        foreach ($this->createdAssetIds as $assetId) {
            $this->client->request(
                'DELETE',
                "/cms-api/v1/admin/assets/{$assetId}",
                [],
                [],
                [
                    'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                    'CONTENT_TYPE' => 'application/json'
                ]
            );
        }

        $this->createdAssetIds = [];
        parent::tearDown();
    }
} 