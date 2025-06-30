<?php

namespace App\Tests\Controller\Api\V1;

use App\Tests\Controller\Api\V1\BaseControllerTest;

/**
 * Test page translation functionality
 */
class PageTranslationTest extends BaseControllerTest
{
    /**
     * Test getting pages without language_id (default behavior)
     */
    public function testGetPagesWithoutLanguageId(): void
    {
        // Make request to get pages without language_id
        $this->client->request('GET', '/cms-api/v1/pages');
        
        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('data', $responseData);
        $this->assertIsArray($responseData['data']);
        
        // Check if pages have title field (if any pages exist)
        if (!empty($responseData['data'])) {
            $firstPage = $responseData['data'][0];
            $this->assertArrayHasKey('title', $firstPage);
        }
    }

    /**
     * Test getting pages with language_id parameter
     */
    public function testGetPagesWithLanguageId(): void
    {
        // Make request to get pages with language ID 2 (English)
        $this->client->request('GET', '/cms-api/v1/pages/2');
        
        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('data', $responseData);
        $this->assertIsArray($responseData['data']);
        
        // Check if pages have title field (if any pages exist)
        if (!empty($responseData['data'])) {
            $firstPage = $responseData['data'][0];
            $this->assertArrayHasKey('title', $firstPage);
        }
    }

    /**
     * Test getting pages with German language_id
     */
    public function testGetPagesWithGermanLanguageId(): void
    {
        // Make request to get pages with German language ID (assuming ID 3 is German)
        $this->client->request('GET', '/cms-api/v1/pages/3');
        
        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('data', $responseData);
        $this->assertIsArray($responseData['data']);
        
        // Check if pages have title field (if any pages exist)
        if (!empty($responseData['data'])) {
            $firstPage = $responseData['data'][0];
            $this->assertArrayHasKey('title', $firstPage);
        }
    }

    /**
     * Test admin pages endpoint with language_id parameter
     */
    public function testAdminGetPagesWithLanguageId(): void
    {
        // First login as admin to get access token
        $accessToken = $this->getAdminAccessToken();
        
        // Make request to get admin pages with language ID 2
        $this->client->request('GET', '/cms-api/v1/admin/pages/2', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $accessToken,
            'CONTENT_TYPE' => 'application/json'
        ]);
        
        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('data', $responseData);
        $this->assertIsArray($responseData['data']);
        
        // Check if pages have title field (if any pages exist)
        if (!empty($responseData['data'])) {
            $firstPage = $responseData['data'][0];
            $this->assertArrayHasKey('title', $firstPage);
        }
    }

    /**
     * Test getting single page with language_id query parameter
     */
    public function testGetSinglePageWithLanguageId(): void
    {
        // First get all pages to find a valid page keyword
        $this->client->request('GET', '/cms-api/v1/pages');
        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        
        if (!empty($responseData['data'])) {
            $firstPage = $responseData['data'][0];
            $pageKeyword = $firstPage['keyword'];
            
            // Now get the specific page with language_id parameter
            $this->client->request('GET', '/cms-api/v1/pages/' . $pageKeyword . '?language_id=2');
            
            $pageResponse = $this->client->getResponse();
            $this->assertSame(200, $pageResponse->getStatusCode());
            $pageResponseData = json_decode($pageResponse->getContent(), true);
            
            $this->assertArrayHasKey('data', $pageResponseData);
            $this->assertArrayHasKey('page', $pageResponseData['data']);
            $this->assertArrayHasKey('keyword', $pageResponseData['data']['page']);
            $this->assertEquals($pageKeyword, $pageResponseData['data']['page']['keyword']);
        } else {
            $this->markTestSkipped('No pages available for testing');
        }
    }

    /**
     * Test invalid language_id format
     */
    public function testGetPagesWithInvalidLanguageId(): void
    {
        // Make request with invalid language_id format (should be numeric)
        $this->client->request('GET', '/cms-api/v1/pages/invalid');
        
        // Should return 404 as the route pattern doesn't match
        $response = $this->client->getResponse();
        $this->assertSame(404, $response->getStatusCode());
    }
} 