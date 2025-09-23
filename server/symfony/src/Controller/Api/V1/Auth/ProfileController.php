<?php

namespace App\Controller\Api\V1\Auth;

use App\Controller\Trait\RequestValidatorTrait;
use App\Entity\User;
use App\Exception\RequestValidationException;
use App\Service\Auth\ProfileService;
use App\Service\Auth\UserContextService;
use App\Service\Auth\UserDataService;
use App\Service\Core\ApiResponseFormatter;
use App\Service\JSON\JsonSchemaValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API V1 Profile Controller
 *
 * Handles user profile management endpoints for API v1
 */
class ProfileController extends AbstractController
{
    use RequestValidatorTrait;

    public function __construct(
        private readonly UserContextService $userContextService,
        private readonly UserDataService $userDataService,
        private readonly ProfileService $profileService,
        private readonly ApiResponseFormatter $responseFormatter,
        private readonly JsonSchemaValidationService $jsonSchemaValidationService,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Update user name
     *
     * @route /auth/user/name
     * @method PUT
     */
    public function updateName(Request $request): JsonResponse
    {
        try {
            // Get the authenticated user
            $currentUser = $this->userContextService->getCurrentUser();
            if (!$currentUser) {
                return $this->responseFormatter->formatError(
                    'User not authenticated',
                    Response::HTTP_UNAUTHORIZED
                );
            }

            // Validate request against JSON schema
            $data = $this->validateRequest($request, 'requests/auth/update_name', $this->jsonSchemaValidationService);

            $newName = $data['name'] ?? null;

            // Update name using the service
            $updatedUser = $this->profileService->updateName($currentUser, $newName);

            // Get updated user data
            $userData = $this->userDataService->getUserData($updatedUser);

            return $this->responseFormatter->formatSuccess(
                $userData,
                'responses/auth/user_data',
                Response::HTTP_OK,
                true
            );
        } catch (RequestValidationException $e) {
            // Let the ApiExceptionListener handle this
            throw $e;
        } catch (\InvalidArgumentException $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception $e) {
            $this->logger->error('Update name error', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->responseFormatter->formatError(
                'An error occurred while updating name.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Update user password
     *
     * @route /auth/user/password
     * @method PUT
     */
    public function updatePassword(Request $request): JsonResponse
    {
        try {
            // Get the authenticated user
            $currentUser = $this->userContextService->getCurrentUser();
            if (!$currentUser) {
                return $this->responseFormatter->formatError(
                    'User not authenticated',
                    Response::HTTP_UNAUTHORIZED
                );
            }

            // Validate request against JSON schema
            $data = $this->validateRequest($request, 'requests/auth/update_password', $this->jsonSchemaValidationService);

            $currentPassword = $data['current_password'] ?? null;
            $newPassword = $data['new_password'] ?? null;

            // Update password using the service
            $this->profileService->updatePassword($currentUser, $currentPassword, $newPassword);

            return $this->responseFormatter->formatSuccess([
                'message' => 'Password updated successfully'
            ], null, Response::HTTP_OK, true);
        } catch (RequestValidationException $e) {
            // Let the ApiExceptionListener handle this
            throw $e;
        } catch (\InvalidArgumentException $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception $e) {
            $this->logger->error('Update password error', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->responseFormatter->formatError(
                'An error occurred while updating password.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Delete user account
     *
     * @route /auth/user/account
     * @method DELETE
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        try {
            // Get the authenticated user
            $currentUser = $this->userContextService->getCurrentUser();
            if (!$currentUser) {
                return $this->responseFormatter->formatError(
                    'User not authenticated',
                    Response::HTTP_UNAUTHORIZED
                );
            }

            // Validate request against JSON schema
            $data = $this->validateRequest($request, 'requests/auth/delete_account', $this->jsonSchemaValidationService);

            $emailConfirmation = $data['email_confirmation'] ?? null;

            // Delete account using the service
            $this->profileService->deleteAccount($currentUser, $emailConfirmation);

            return $this->responseFormatter->formatSuccess([
                'message' => 'Account deleted successfully'
            ], null, Response::HTTP_OK, false);
        } catch (RequestValidationException $e) {
            // Let the ApiExceptionListener handle this
            throw $e;
        } catch (\InvalidArgumentException $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception $e) {
            $this->logger->error('Delete account error', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->responseFormatter->formatError(
                'An error occurred while deleting account.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
