<?php

namespace App\Tests\Controller\Api\V1\Traits;

use App\Service\Core\LookupService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait ManagesTestPagesTrait
 * 
 * Provides common methods for creating and deleting test pages in API controller tests.
 */
trait ManagesTestPagesTrait
{
    /**
     * Creates a test page with the given keyword.
     *
     * @param string $pageKeyword The unique keyword for the page.
     * @param string $pageAccessTypeCode The access type code (e.g., LookupService::PAGE_ACCESS_TYPES_MOBILE_AND_WEB).
     * @param array $overridePayload Optional payload to override defaults.
     */
    protected function createTestPageWithKeyword(string $pageKeyword, ?string $pageAccessTypeCode = null, array $overridePayload = []): void
    {
        $token = $this->getAdminAccessToken();
        
        // Assign default pageAccessTypeCode if not provided
        $finalPageAccessTypeCode = $pageAccessTypeCode ?? LookupService::PAGE_ACCESS_TYPES_WEB; // Corrected constant

        $defaultPayload = [
            'keyword' => $pageKeyword,
            'pageAccessTypeCode' => $finalPageAccessTypeCode,
            'headless' => false,
            'openAccess' => true, // Defaulting to true as seen in AdminPageControllerTest
            'url' => '/' . $pageKeyword,
            'navPosition' => 100, // Default nav position
            'footerPosition' => null,
            'parent' => null,
            // 'pageTypeId' is intentionally omitted as AdminPageService hardcodes it to 'experiment'
        ];
        
        $payload = array_merge($defaultPayload, $overridePayload);

        $this->client->request(
            'POST',
            '/cms-api/v1/admin/pages',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode(), "Failed to create test page '{$pageKeyword}': " . $response->getContent());

        // Basic validation of the response structure
        $data = json_decode($response->getContent());
        $this->assertTrue(property_exists($data, 'data'), 'Create page response does not have data property for ' . $pageKeyword);
        $this->assertTrue(property_exists($data->data, 'keyword'), 'Create page response data does not have keyword property for ' . $pageKeyword);
        $this->assertSame($pageKeyword, $data->data->keyword, 'Returned page keyword does not match for ' . $pageKeyword);
        
        // Schema validation (optional, can be added if a generic 'page_created' schema exists)
        // $validationErrors = $this->jsonSchemaValidationService->validate($data, 'responses/admin/page_created_success'); // Example schema
        // $this->assertEmpty($validationErrors, "Create page '{$pageKeyword}' response failed schema validation: " . implode("\n", $validationErrors));
    }

    /**
     * Deletes the test page with the given keyword.
     *
     * @param string $pageKeyword The keyword of the page to delete.
     */
    protected function deleteTestPageWithKeyword(string $pageKeyword): void
    {
        $token = $this->getAdminAccessToken();

        $this->client->request(
            'DELETE',
            '/cms-api/v1/admin/pages/' . $pageKeyword,
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json']
        );

        $response = $this->client->getResponse();
        // Deleting a non-existent page might also be OK or return 404, depending on API design.
        // For now, strict check for HTTP_OK which implies it existed and was deleted.
        // If the page might not exist, consider checking for [Response::HTTP_OK, Response::HTTP_NOT_FOUND]
        // or use deleteTestPageIfExistsWithKeyword.
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), "Failed to delete test page '{$pageKeyword}': " . $response->getContent());

        // Basic validation of the response structure
        $data = json_decode($response->getContent());
        $this->assertTrue(property_exists($data, 'data'), 'Delete page response does not have data property for ' . $pageKeyword);
        $this->assertTrue(property_exists($data->data, 'keyword'), 'Delete page response data does not have keyword property for ' . $pageKeyword);
        $this->assertSame($pageKeyword, $data->data->keyword, 'Returned keyword does not match on delete for ' . $pageKeyword);
    }

    /**
     * Helper method to delete the test page with the given keyword if it exists.
     * This ensures tests can be run multiple times without failing due to pre-existing state.
     *
     * @param string $pageKeyword The keyword of the page to delete if it exists.
     */
    protected function deleteTestPageIfExistsWithKeyword(string $pageKeyword): void
    {
        $token = $this->getAdminAccessToken();

        $this->client->request(
            'GET',
            '/cms-api/v1/admin/pages/' . $pageKeyword,
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json']
        );

        $response = $this->client->getResponse();

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $this->client->request(
                'DELETE',
                '/cms-api/v1/admin/pages/' . $pageKeyword,
                [],
                [],
                ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json']
            );
            $deleteResponse = $this->client->getResponse();
            $this->assertSame(Response::HTTP_OK, $deleteResponse->getStatusCode(), "Failed to delete existing page '{$pageKeyword}' during cleanup: " . $deleteResponse->getContent());
            // Consider a small sleep if there are race conditions with immediate recreation, though ideally not needed.
            // sleep(1);
        }
    }
}
