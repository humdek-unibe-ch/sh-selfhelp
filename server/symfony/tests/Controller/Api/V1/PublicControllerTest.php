<?php
namespace App\Tests\Controller\Api\V1;

use Symfony\Component\HttpFoundation\Response;

class PublicControllerTest extends BaseControllerTest
{

    /**
     * @group public
     */
    public function testGetPublicPages(): void
    {
        $this->client->request('GET', '/cms-api/v1/pages');
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Public get pages failed.');
        
        // Decode as object (not array) for schema validation
        $data = json_decode($response->getContent());
        $this->assertTrue(property_exists($data, 'data'), 'Response does not have data property');

        // Validate response against JSON schema
        $validationErrors = $this->jsonSchemaValidationService->validate(
            $data, // Validate the full response object
            'responses/common/_acl_page_definition'
        );
        $this->assertEmpty($validationErrors, "Response for /cms-api/v1/pages failed schema validation:\n" . implode("\n", $validationErrors));
    }

    /**
     * @group public
     */
    public function testGetPublicPageBySlugOrId(): void
    {
        // First, get a list of public pages to find a valid slug or ID
        $this->client->request('GET', '/cms-api/v1/pages');
        $listResponse = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $listResponse->getStatusCode(), 'Failed to get public pages list.');
        
        // For the list, we still need array access to find a page with a slug
        $listData = json_decode($listResponse->getContent(), true);

        $this->assertArrayHasKey('data', $listData, 'No data key in public pages list response.');
        $this->assertIsArray($listData['data'], 'Data in public pages list is not an array.');
        $this->assertNotEmpty($listData['data'], 'No pages found in public list to test single page retrieval.');

        // Find a page with a non-empty slug to test detail endpoint
        $pageWithSlug = null;
        foreach ($listData['data'] as $page) {
            if (!empty($page['keyword'])) {
                $pageWithSlug = $page;
                break;
            }
        }
        
        // Skip test if no page with slug is found
        if (null === $pageWithSlug) {
            $this->markTestSkipped('No pages with a valid slug found in test database');
        }
        
        // Use slug as page_keyword (more reliable than numeric ID)
        $pageKeyword = $pageWithSlug['keyword'];
        $this->assertNotEmpty($pageKeyword, 'Could not determine page keyword from response');

        // Now request the specific page using the fetched keyword
        $this->client->request('GET', '/cms-api/v1/pages/' . $pageKeyword);
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Public get page by keyword (' . $pageKeyword . ') failed: ' . $response->getContent());
        
        // Decode as object (not array) for schema validation
        $data = json_decode($response->getContent());
        $this->assertTrue(property_exists($data, 'data'), 'Response does not have data property');

        // Validate response against JSON schema
        $validationErrors = $this->jsonSchemaValidationService->validate(
            $data, // Validate the full response object
            'responses/frontend/get_page'
        );
        $this->assertEmpty($validationErrors, "Response for /cms-api/v1/pages/" . $pageKeyword . " failed schema validation:\n" . implode("\n", $validationErrors));
    }
}
