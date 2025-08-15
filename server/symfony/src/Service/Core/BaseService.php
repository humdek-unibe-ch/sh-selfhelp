<?php

namespace App\Service\Core;

use App\Exception\ServiceException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base service with error handling and caching capabilities
 */
abstract class BaseService
{
    use CacheableServiceTrait;
    /**
     * Throw a not found exception
     */
    protected function throwNotFound(string $message = 'Resource not found'): void
    {
        throw new ServiceException($message, Response::HTTP_NOT_FOUND);
    }
    
    /**
     * Throw a forbidden exception
     */
    protected function throwForbidden(string $message = 'Access denied'): void
    {
        throw new ServiceException($message, Response::HTTP_FORBIDDEN);
    }
    
    /**
     * Throw a bad request exception
     */
    protected function throwBadRequest(string $message = 'Bad request'): void
    {
        throw new ServiceException($message, Response::HTTP_BAD_REQUEST);
    }
    
    /**
     * Throw a validation exception with validation errors
     */
    protected function throwValidationError(string $message = 'Validation failed', array $errors = []): void
    {
        throw new ServiceException($message, Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }
    
    /**
     * Throw a conflict exception
     */
    protected function throwConflict(string $message = 'Resource already exists'): void
    {
        throw new ServiceException($message, Response::HTTP_CONFLICT);
    }
    
    /**
     * Check if a user is logged in
     * This method should be overridden by child classes that have access to authentication
     */
    protected function isUserLoggedIn(): bool
    {
        return false;
    }
}
