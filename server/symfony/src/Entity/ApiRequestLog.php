<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'apiRequestLogs')]
class ApiRequestLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(name: 'route_name', type: 'string', length: 255, nullable: true)]
    private ?string $routeName = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $path;

    #[ORM\Column(type: 'string', length: 10)]
    private string $method;

    #[ORM\Column(name: 'status_code', type: 'integer')]
    private int $statusCode;

    #[ORM\Column(name: 'user_id', type: 'integer', nullable: true)]
    private ?int $userId = null;

    #[ORM\Column(name: 'ip_address', type: 'string', length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(name: 'request_time', type: 'datetime')]
    private \DateTimeInterface $requestTime;

    #[ORM\Column(name: 'response_time', type: 'datetime')]
    private \DateTimeInterface $responseTime;

    #[ORM\Column(name: 'duration_ms', type: 'integer')]
    private int $durationMs;

    #[ORM\Column(name: 'request_params', type: 'text', nullable: true)]
    private ?string $requestParams = null;

    #[ORM\Column(name: 'request_headers', type: 'text', nullable: true)]
    private ?string $requestHeaders = null;

    #[ORM\Column(name: 'response_data', type: 'text', nullable: true)]
    private ?string $responseData = null;

    #[ORM\Column(name: 'error_message', type: 'text', nullable: true)]
    private ?string $errorMessage = null;

    // Getters and setters

    public function getId(): int
    {
        return $this->id;
    }

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    public function setRouteName(?string $routeName): self
    {
        $this->routeName = $routeName;
        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;
        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getRequestTime(): \DateTimeInterface
    {
        return $this->requestTime;
    }

    public function setRequestTime(\DateTimeInterface $requestTime): self
    {
        $this->requestTime = $requestTime;
        return $this;
    }

    public function getResponseTime(): \DateTimeInterface
    {
        return $this->responseTime;
    }

    public function setResponseTime(\DateTimeInterface $responseTime): self
    {
        $this->responseTime = $responseTime;
        return $this;
    }

    public function getDurationMs(): int
    {
        return $this->durationMs;
    }

    public function setDurationMs(int $durationMs): self
    {
        $this->durationMs = $durationMs;
        return $this;
    }

    public function getRequestParams(): ?string
    {
        return $this->requestParams;
    }

    public function setRequestParams(?string $requestParams): self
    {
        $this->requestParams = $requestParams;
        return $this;
    }

    public function getRequestHeaders(): ?string
    {
        return $this->requestHeaders;
    }

    public function setRequestHeaders(?string $requestHeaders): self
    {
        $this->requestHeaders = $requestHeaders;
        return $this;
    }

    public function getResponseData(): ?string
    {
        return $this->responseData;
    }

    public function setResponseData(?string $responseData): self
    {
        $this->responseData = $responseData;
        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): self
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }
}
