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
        $client = static::createClient();
        
        // Make request to get pages without language_id
        $client->request('GET', '/cms-api/v1/pages');
        
        $this->assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('data', $response);
        $this->assertIsArray($response['data']);
        
        // Check if pages have title field (if any pages exist)
        if (!empty($response['data'])) {
            $firstPage = $response['data'][0];
            $this->assertArrayHasKey('title', $firstPage);
        }
    }

    /**
     * Test getting pages with language_id parameter
     */
    public function testGetPagesWithLanguageId(): void
    {
        $client = static::createClient();
        
        // Make request to get pages with language ID 2 (English)
        $client->request('GET', '/cms-api/v1/pages/2');
        
        $this->assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('data', $response);
        $this->assertIsArray($response['data']);
        
        // Check if pages have title field (if any pages exist)
        if (!empty($response['data'])) {
            $firstPage = $response['data'][0];
            $this->assertArrayHasKey('title', $firstPage);
        }
    }

    /**
     * Test getting pages with German language_id
     */
    public function testGetPagesWithGermanLanguageId(): void
    {
        $client = static::createClient();
        
        // Make request to get pages with German language ID (assuming ID 3 is German)
        $client->request('GET', '/cms-api/v1/pages/3');
        
        $this->assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('data', $response);
        $this->assertIsArray($response['data']);
        
        // Check if pages have title field (if any pages exist)
        if (!empty($response['data'])) {
            $firstPage = $response['data'][0];
            $this->assertArrayHasKey('title', $firstPage);
        }
    }

    /**
     * Test admin pages endpoint with language_id parameter
     */
    public function testAdminGetPagesWithLanguageId(): void
    {
        $client = static::createClient();
        
        // First login as admin to get access token
        // ... (login logic would be here in a real test)
        
        // Make request to get admin pages with language ID 2
        $client->request('GET', '/cms-api/v1/admin/pages/2');
        
        $this->assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('data', $response);
        $this->assertIsArray($response['data']);
        
        // Check if pages have title field (if any pages exist)
        if (!empty($response['data'])) {
            $firstPage = $response['data'][0];
            $this->assertArrayHasKey('title', $firstPage);
        }
    }

    /**
     * Test getting single page with language_id query parameter
     */
    public function testGetSinglePageWithLanguageId(): void
    {
        $client = static::createClient();
        
        // First get all pages to find a valid page keyword
        $client->request('GET', '/cms-api/v1/pages');
        $response = json_decode($client->getResponse()->getContent(), true);
        
        if (!empty($response['data'])) {
            $firstPage = $response['data'][0];
            $pageKeyword = $firstPage['keyword'];
            
            // Now get the specific page with language_id parameter
            $client->request('GET', '/cms-api/v1/pages/' . $pageKeyword . '?language_id=2');
            
            $this->assertResponseIsSuccessful();
            $pageResponse = json_decode($client->getResponse()->getContent(), true);
            
            $this->assertArrayHasKey('data', $pageResponse);
            $this->assertArrayHasKey('keyword', $pageResponse['data']);
            $this->assertEquals($pageKeyword, $pageResponse['data']['keyword']);
        } else {
            $this->markTestSkipped('No pages available for testing');
        }
    }

    /**
     * Test invalid language_id format
     */
    public function testGetPagesWithInvalidLanguageId(): void
    {
        $client = static::createClient();
        
        // Make request with invalid language_id format (should be numeric)
        $client->request('GET', '/cms-api/v1/pages/invalid');
        
        // Should return 404 as the route pattern doesn't match
        $this->assertResponseStatusCodeSame(404);
    }
} 