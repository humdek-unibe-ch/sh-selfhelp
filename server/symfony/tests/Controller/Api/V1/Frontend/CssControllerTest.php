<?php

namespace App\Tests\Controller\Api\V1\Frontend;

use App\Tests\Controller\Api\V1\BaseControllerTest;
use Symfony\Component\HttpFoundation\Response;

class CssControllerTest extends BaseControllerTest
{
    public function testGetCssClassesReturnsValidResponse(): void
    {
        // Make request to the CSS classes endpoint
        $this->client->request('GET', '/cms-api/v1/frontend/css-classes');
        
        $response = $this->client->getResponse();
        
        // Assert response is successful
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        
        // Parse JSON response
        $responseData = json_decode($response->getContent(), true);
        
        // Assert response structure
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertArrayHasKey('logged_in', $responseData);
        $this->assertArrayHasKey('meta', $responseData);
        $this->assertArrayHasKey('data', $responseData);
        
        // Assert status is 200
        $this->assertEquals(200, $responseData['status']);
        
        // Assert data contains classes array
        $this->assertArrayHasKey('classes', $responseData['data']);
        $this->assertIsArray($responseData['data']['classes']);
        
        // Assert classes array is not empty (should have fallback classes at minimum)
        $this->assertGreaterThan(0, count($responseData['data']['classes']));
        
        // Assert all classes are strings
        foreach ($responseData['data']['classes'] as $class) {
            $this->assertIsString($class);
        }
    }

    public function testGetCssClassesHasOpenAccess(): void
    {
        // Test without authentication - should still work
        $this->client->request('GET', '/cms-api/v1/frontend/css-classes');
        
        $response = $this->client->getResponse();
        
        // Should return 200 even without authentication
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        
        // Should indicate not logged in but still return data
        $this->assertFalse($responseData['logged_in']);
        $this->assertArrayHasKey('classes', $responseData['data']);
    }

    public function testGetCssClassesReturnsValidClasses(): void
    {
        $this->client->request('GET', '/cms-api/v1/frontend/css-classes');
        
        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        
        $classes = $responseData['data']['classes'];
        
        // Just verify we get some valid CSS classes
        $this->assertGreaterThan(0, count($classes), "Should return at least one CSS class");
        
        // Check that all classes are valid strings
        foreach ($classes as $class) {
            $this->assertIsString($class, "Each class should be a string");
            $this->assertNotEmpty($class, "Each class should not be empty");
        }
    }
} 