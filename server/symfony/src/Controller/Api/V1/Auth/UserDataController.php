<?php

namespace App\Controller\Api\V1\Auth;

use App\Service\Auth\UserContextService;
use App\Service\Auth\UserDataService;
use App\Service\Core\ApiResponseFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * API V1 User Data Controller
 * 
 * Handles user data retrieval endpoints for API v1
 * Provides user data that was previously embedded in JWT tokens
 */
class UserDataController extends AbstractController
{
    public function __construct(
        private readonly UserContextService $userContextService,
        private readonly UserDataService $userDataService,
        private readonly ApiResponseFormatter $responseFormatter
    ) {
    }

    /**
     * Get current user data including roles, permissions, and language
     * 
     * @route /auth/user-data
     * @method GET
     */
    public function getCurrentUserData(): JsonResponse
    {
        try {
            $currentUser = $this->userContextService->getCurrentUser();
            
            if (!$currentUser) {
                return $this->responseFormatter->formatError(
                    'User not authenticated',
                    Response::HTTP_UNAUTHORIZED
                );
            }

            $userData = $this->userDataService->getUserData($currentUser);

            return $this->responseFormatter->formatSuccess(
                $userData,
                'responses/auth/user_data',
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                'Failed to retrieve user data: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
