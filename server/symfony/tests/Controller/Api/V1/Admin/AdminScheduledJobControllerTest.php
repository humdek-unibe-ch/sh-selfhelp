<?php

namespace App\Tests\Controller\Api\V1\Admin;

use App\Tests\Controller\Api\V1\BaseControllerTest;
use Symfony\Component\HttpFoundation\Response;

class AdminScheduledJobControllerTest extends BaseControllerTest
{
    public function testGetScheduledJobs(): void
    {
        $this->client->request('GET', '/api/v1/admin/scheduled-jobs', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $this->getAdminAccessToken()
        ]);

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('success', $response['status']);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('scheduledJobs', $response['data']);
        $this->assertArrayHasKey('totalCount', $response['data']);
        $this->assertArrayHasKey('page', $response['data']);
        $this->assertArrayHasKey('pageSize', $response['data']);
        $this->assertArrayHasKey('totalPages', $response['data']);
    }

    public function testGetScheduledJobsWithFilters(): void
    {
        $this->client->request('GET', '/api/v1/admin/scheduled-jobs?page=1&pageSize=10&search=test&status=Queued&dateType=date_to_be_executed', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $this->getAdminAccessToken()
        ]);

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('success', $response['status']);
    }

    public function testGetScheduledJobById(): void
    {
        // First get a list to find an existing job ID
        $this->client->request('GET', '/api/v1/admin/scheduled-jobs', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $this->getAdminAccessToken()
        ]);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $jobs = $response['data']['scheduledJobs'];
        
        if (empty($jobs)) {
            $this->markTestSkipped('No scheduled jobs available for testing');
        }

        $jobId = $jobs[0]['id'];

        $this->client->request('GET', "/api/v1/admin/scheduled-jobs/{$jobId}", [], [], [
            'HTTP_Authorization' => 'Bearer ' . $this->getAdminAccessToken()
        ]);

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('success', $response['status']);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('id', $response['data']);
        $this->assertEquals($jobId, $response['data']['id']);
    }

    public function testGetScheduledJobByIdNotFound(): void
    {
        $this->client->request('GET', '/api/v1/admin/scheduled-jobs/999999', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $this->getAdminAccessToken()
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('error', $response['status']);
    }

    public function testExecuteScheduledJob(): void
    {
        // First get a list to find a queued job ID
        $this->client->request('GET', '/api/v1/admin/scheduled-jobs?status=Queued', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $this->getAdminAccessToken()
        ]);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $jobs = $response['data']['scheduledJobs'];
        
        if (empty($jobs)) {
            $this->markTestSkipped('No queued scheduled jobs available for testing');
        }

        $jobId = $jobs[0]['id'];

        $this->client->request('POST', "/api/v1/admin/scheduled-jobs/{$jobId}/execute", [], [], [
            'HTTP_Authorization' => 'Bearer ' . $this->getAdminAccessToken()
        ]);

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('success', $response['status']);
        $this->assertArrayHasKey('message', $response);
        $this->assertEquals('Job executed successfully', $response['message']);
    }

    public function testDeleteScheduledJob(): void
    {
        // First get a list to find an existing job ID
        $this->client->request('GET', '/api/v1/admin/scheduled-jobs', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $this->getAdminAccessToken()
        ]);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $jobs = $response['data']['scheduledJobs'];
        
        if (empty($jobs)) {
            $this->markTestSkipped('No scheduled jobs available for testing');
        }

        $jobId = $jobs[0]['id'];

        $this->client->request('DELETE', "/api/v1/admin/scheduled-jobs/{$jobId}", [], [], [
            'HTTP_Authorization' => 'Bearer ' . $this->getAdminAccessToken()
        ]);

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('success', $response['status']);
        $this->assertArrayHasKey('message', $response);
        $this->assertEquals('Job deleted successfully', $response['message']);
    }

    public function testGetJobTransactions(): void
    {
        // First get a list to find an existing job ID
        $this->client->request('GET', '/api/v1/admin/scheduled-jobs', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $this->getAdminAccessToken()
        ]);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $jobs = $response['data']['scheduledJobs'];
        
        if (empty($jobs)) {
            $this->markTestSkipped('No scheduled jobs available for testing');
        }

        $jobId = $jobs[0]['id'];

        $this->client->request('GET', "/api/v1/admin/scheduled-jobs/{$jobId}/transactions", [], [], [
            'HTTP_Authorization' => 'Bearer ' . $this->getAdminAccessToken()
        ]);

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('success', $response['status']);
        $this->assertArrayHasKey('data', $response);
        $this->assertIsArray($response['data']);
    }
    public function testUnauthorizedAccess(): void
    {
        $this->client->request('GET', '/cms-api/v1/admin/scheduled-jobs');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
} 