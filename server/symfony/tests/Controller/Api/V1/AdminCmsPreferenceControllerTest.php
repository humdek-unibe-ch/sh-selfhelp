<?php

namespace App\Tests\Controller\Api\V1;

use Symfony\Component\HttpFoundation\Response;

class AdminCmsPreferenceControllerTest extends BaseControllerTest
{
    /**
     * @group cms-preferences
     */
    public function testGetCmsPreferencesSuccess(): void
    {
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/cms-preferences',
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
        $this->assertArrayHasKey('id', $data['data']);
        $this->assertArrayHasKey('callback_api_key', $data['data']);
        $this->assertArrayHasKey('default_language_id', $data['data']);
        $this->assertArrayHasKey('default_language', $data['data']);
        $this->assertArrayHasKey('anonymous_users', $data['data']);
        $this->assertArrayHasKey('firebase_config', $data['data']);
        
        // Validate data types
        $this->assertIsInt($data['data']['id']);
        $this->assertIsInt($data['data']['anonymous_users']);
        
        // Default language can be null or object
        if ($data['data']['default_language'] !== null) {
            $this->assertIsArray($data['data']['default_language']);
            $this->assertArrayHasKey('id', $data['data']['default_language']);
            $this->assertArrayHasKey('locale', $data['data']['default_language']);
            $this->assertArrayHasKey('language', $data['data']['default_language']);
        }
    }

    /**
     * @group cms-preferences
     */
    public function testUpdateCmsPreferencesSuccess(): void
    {
        $updateData = [
            'anonymous_users' => 1,
            'callback_api_key' => 'test-api-key-updated'
        ];

        $this->client->request(
            'PUT',
            '/cms-api/v1/admin/cms-preferences',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($updateData)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        
        // Validate response structure
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('data', $data);
        
        // Validate updated values
        $this->assertSame($updateData['anonymous_users'], $data['data']['anonymous_users']);
        $this->assertSame($updateData['callback_api_key'], $data['data']['callback_api_key']);
    }

    /**
     * @group cms-preferences
     */
    public function testUpdateCmsPreferencesPartial(): void
    {
        // First get current preferences
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/cms-preferences',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $getCurrentResponse = $this->client->getResponse();
        $currentData = json_decode($getCurrentResponse->getContent(), true);
        $originalAnonymousUsers = $currentData['data']['anonymous_users'];

        // Update only one field
        $updateData = [
            'callback_api_key' => 'partial-update-test'
        ];

        $this->client->request(
            'PUT',
            '/cms-api/v1/admin/cms-preferences',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($updateData)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        
        // Validate that only the updated field changed
        $this->assertSame($updateData['callback_api_key'], $data['data']['callback_api_key']);
        $this->assertSame($originalAnonymousUsers, $data['data']['anonymous_users']);
    }

    /**
     * @group cms-preferences
     */
    public function testUpdateCmsPreferencesInvalidLanguage(): void
    {
        $updateData = [
            'default_language_id' => 99999 // Non-existent language ID
        ];

        $this->client->request(
            'PUT',
            '/cms-api/v1/admin/cms-preferences',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getAdminAccessToken(),
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($updateData)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * @group cms-preferences
     */
    public function testGetCmsPreferencesUnauthorized(): void
    {
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/cms-preferences',
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
     * @group cms-preferences
     */
    public function testUpdateCmsPreferencesUnauthorized(): void
    {
        $updateData = [
            'anonymous_users' => 1
        ];

        $this->client->request(
            'PUT',
            '/cms-api/v1/admin/cms-preferences',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($updateData)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
} 