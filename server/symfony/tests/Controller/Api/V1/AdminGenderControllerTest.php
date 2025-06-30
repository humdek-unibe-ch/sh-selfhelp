<?php

namespace App\Tests\Controller\Api\V1;

use Symfony\Component\HttpFoundation\Response;

class AdminGenderControllerTest extends BaseControllerTest
{
    /**
     * @group gender-management
     */
    public function testGetAllGendersSuccess(): void
    {
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/genders',
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
        $this->assertArrayHasKey('genders', $data['data']);
        
        // Validate genders array
        $this->assertIsArray($data['data']['genders']);
        
        if (!empty($data['data']['genders'])) {
            $gender = $data['data']['genders'][0];
            $this->assertArrayHasKey('id', $gender);
            $this->assertArrayHasKey('name', $gender);
            $this->assertIsInt($gender['id']);
            $this->assertIsString($gender['name']);
        }
    }

    /**
     * @group gender-management
     */
    public function testGetAllGendersUnauthorized(): void
    {
        $this->client->request(
            'GET',
            '/cms-api/v1/admin/genders',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
} 