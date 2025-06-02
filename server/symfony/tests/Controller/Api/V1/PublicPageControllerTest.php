<?php
namespace App\Tests\Controller\Api\V1;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class PublicPageControllerTest extends WebTestCase
{
    protected $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * @group public
     */
    public function testGetPublicPages(): void
    {
        $this->client->request('GET', '/cms-api/v1/pages');
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Public get pages failed.');
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
    }

    /**
     * @group public
     */
    public function testGetPublicPageBySlugOrId(): void
    {
        $this->client->request('GET', '/cms-api/v1/pages/open');
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Public get page by slug_or_id failed.');
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
    }
}
