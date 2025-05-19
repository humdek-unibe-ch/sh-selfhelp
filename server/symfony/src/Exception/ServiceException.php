<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exception thrown by services with additional data and status code
 */
class ServiceException extends \Exception
{
    private ?array $data;
    
    public function __construct(string $message, int $code = Response::HTTP_BAD_REQUEST, ?array $data = null) 
    {
        parent::__construct($message, $code);
        $this->data = $data;
    }
    
    public function getData(): ?array 
    {
        return $this->data;
    }
}
