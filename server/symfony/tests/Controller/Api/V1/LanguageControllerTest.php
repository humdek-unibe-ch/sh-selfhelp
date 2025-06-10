<?php

namespace App\Tests\Controller\Api\V1;

use App\Entity\Language;
use Doctrine\ORM\EntityManagerInterface;

class LanguageControllerTest extends BaseControllerTest
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testGetAllLanguages(): void
    {
        // Get a user token
        $token = $this->getAdminAccessToken();

        // Make the API request
        $this->client->request(
            'GET',
            '/cms-api/v1/languages',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json']
        );

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();
        
        // Validate response structure
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $content);
        $this->assertIsArray($content['data']);
        
        // Validate schema
        $validationErrors = $this->jsonSchemaValidationService->validate(
            $content,
            'responses/languages/get_languages'
        );
        $this->assertEmpty($validationErrors);
        
        // Check that we only get languages with ID > 1
        foreach ($content['data'] as $language) {
            $this->assertGreaterThan(1, $language['id']);
        }
    }

    public function testAdminGetAllLanguages(): void
    {
        // Get an admin token
        $token = $this->getAdminAccessToken();

        // Make the API request
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/languages',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json']
        );

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();
        
        // Validate response structure
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $content);
        $this->assertIsArray($content['data']);
        
        // Validate schema
        $validationErrors = $this->jsonSchemaValidationService->validate(
            $content,
            'responses/languages/get_languages'
        );
        $this->assertEmpty($validationErrors);
        
        // Check that we get all languages including ID = 1
        $hasDefaultLanguage = false;
        foreach ($content['data'] as $language) {
            if ($language['id'] === 1) {
                $hasDefaultLanguage = true;
                break;
            }
        }
        $this->assertTrue($hasDefaultLanguage, 'Default language (ID=1) should be included in admin response');
    }

    public function testAdminCreateUpdateDeleteLanguage(): void
    {
        // Get an admin token
        $token = $this->getAdminAccessToken();

        // 1. Create a new language
        $createData = [
            'locale' => 'es-test',
            'language' => 'Spanish Test',
            'csv_separator' => ';',
        ];

        $this->client->request(
            'POST',
            '/cms-api/v1/admin/languages',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json'],
            json_encode($createData)
        );

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $content);
        
        // Validate schema
        $validationErrors = $this->jsonSchemaValidationService->validate(
            $content,
            'responses/languages/language'
        );
        $this->assertEmpty($validationErrors);
        
        // Get the created language ID
        $languageId = $content['data']['id'];
        
        // 2. Update the language
        $updateData = [
            'locale' => 'es-test-updated',
            'language' => 'Spanish Test Updated',
            'csv_separator' => ';',
        ];

        $this->client->request(
            'PUT',
            '/cms-api/v1/admin/languages/' . $languageId,
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $content);
        $this->assertEquals('es-test-updated', $content['data']['locale']);
        $this->assertEquals('Spanish Test Updated', $content['data']['language']);
        
        // 3. Delete the language
        $this->client->request(
            'DELETE',
            '/cms-api/v1/admin/languages/' . $languageId,
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json']
        );

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $content);
        $this->assertEquals($languageId, $content['data']['id']);
        
        // Validate schema
        $validationErrors = $this->jsonSchemaValidationService->validate(
            $content,
            'responses/languages/language'
        );
        $this->assertEmpty($validationErrors);
        
        // Verify the language is deleted
        $language = $this->entityManager->getRepository(Language::class)->find($languageId);
        $this->assertNull($language);
    }

    public function testCannotDeleteDefaultLanguage(): void
    {
        // Get an admin token
        $token = $this->getAdminAccessToken();

        // Try to delete the default language (ID = 1)
        $this->client->request(
            'DELETE',
            '/cms-api/v1/admin/languages/1',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json']
        );

        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $content);
        $this->assertEquals('Cannot delete the default language', $content['error']);
    }

    public function testCannotUpdateDefaultLanguage(): void
    {
        // Get an admin token
        $token = $this->getAdminAccessToken();

        // Try to update the default language (ID = 1)
        $updateData = [
            'locale' => 'en-US',
            'language' => 'English (US)'
        ];

        $this->client->request(
            'PUT',
            '/cms-api/v1/admin/languages/1',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $content);
        $this->assertEquals('Cannot update the default language', $content['error']);
    }

    public function testNonAdminCannotAccessAdminEndpoints(): void
    {
        // Get a regular user token
        $token = $this->getAdminAccessToken();

        // Try to access admin endpoint
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/languages',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json']
        );

        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
    }
}
