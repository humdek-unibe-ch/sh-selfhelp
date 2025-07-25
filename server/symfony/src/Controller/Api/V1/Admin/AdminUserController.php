<?php

namespace App\Controller\Api\V1\Admin;

use App\Controller\Trait\RequestValidatorTrait;
use App\Service\CMS\Admin\AdminUserService;
use App\Service\Core\ApiResponseFormatter;
use App\Service\JSON\JsonSchemaValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin User Controller
 * 
 * Handles all user management operations for admin interface
 */
class AdminUserController extends AbstractController
{
    use RequestValidatorTrait;

    public function __construct(
        private readonly AdminUserService $adminUserService,
        private readonly ApiResponseFormatter $responseFormatter,
        private readonly JsonSchemaValidationService $jsonSchemaValidationService
    ) {
    }

    /**
     * Get users with pagination, search, and sorting
     * 
     * @route /admin/users
     * @method GET
     * @param page: which page of results (default: 1)
     * @param pageSize: how many users per page (default: 20, max: 100)
     * @param search: search term for email, name, or username
     * @param sort: sort field (email, name, last_login, blocked, user_type)
     * @param sortDirection: asc or desc (default: asc)
     */
    public function getUsers(Request $request): JsonResponse
    {
        try {
            $page = (int)$request->query->get('page', 1);
            $pageSize = (int)$request->query->get('pageSize', 20);
            $search = $request->query->get('search');
            $sort = $request->query->get('sort');
            $sortDirection = $request->query->get('sortDirection', 'asc');

            $result = $this->adminUserService->getUsers($page, $pageSize, $search, $sort, $sortDirection);

            return $this->responseFormatter->formatSuccess($result);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get single user by ID
     * 
     * @route /admin/users/{userId}
     * @method GET
     */
    public function getUserById(int $userId): JsonResponse
    {
        try {
            $user = $this->adminUserService->getUserById($userId);
            return $this->responseFormatter->formatSuccess($user);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Create new user
     * 
     * @route /admin/users
     * @method POST
     */
    public function createUser(Request $request): JsonResponse
    {
        try {
            $data = $this->validateRequest($request, 'requests/admin/create_user', $this->jsonSchemaValidationService);
            
            $user = $this->adminUserService->createUser($data);
            
            return $this->responseFormatter->formatSuccess(
                $user,
                null,
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Update existing user
     * 
     * @route /admin/users/{userId}
     * @method PUT
     */
    public function updateUser(int $userId, Request $request): JsonResponse
    {
        try {
            $data = $this->validateRequest($request, 'requests/admin/update_user', $this->jsonSchemaValidationService);
            
            $user = $this->adminUserService->updateUser($userId, $data);
            
            return $this->responseFormatter->formatSuccess($user);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Delete user
     * 
     * @route /admin/users/{userId}
     * @method DELETE
     */
    public function deleteUser(int $userId): JsonResponse
    {
        try {
            $this->adminUserService->deleteUser($userId);
            
            return $this->responseFormatter->formatSuccess(['deleted' => true]);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Block/Unblock user
     * 
     * @route /admin/users/{userId}/block
     * @method PATCH
     */
    public function toggleUserBlock(int $userId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $blocked = $data['blocked'] ?? true;
            
            $user = $this->adminUserService->toggleUserBlock($userId, $blocked);
            
            return $this->responseFormatter->formatSuccess($user);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get user groups
     * 
     * @route /admin/users/{userId}/groups
     * @method GET
     */
    public function getUserGroups(int $userId): JsonResponse
    {
        try {
            $groups = $this->adminUserService->getUserGroups($userId);
            return $this->responseFormatter->formatSuccess(['groups' => $groups]);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get user roles
     * 
     * @route /admin/users/{userId}/roles
     * @method GET
     */
    public function getUserRoles(int $userId): JsonResponse
    {
        try {
            $roles = $this->adminUserService->getUserRoles($userId);
            return $this->responseFormatter->formatSuccess(['roles' => $roles]);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Add groups to user
     * 
     * @route /admin/users/{userId}/groups
     * @method POST
     */
    public function addGroupsToUser(int $userId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $groupIds = $data['group_ids'] ?? [];
            
            if (!is_array($groupIds) || empty($groupIds)) {
                return $this->responseFormatter->formatError(
                    'group_ids array is required',
                    Response::HTTP_BAD_REQUEST
                );
            }
            
            $groups = $this->adminUserService->addGroupsToUser($userId, $groupIds);
            return $this->responseFormatter->formatSuccess(['groups' => $groups]);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Remove groups from user
     * 
     * @route /admin/users/{userId}/groups
     * @method DELETE
     */
    public function removeGroupsFromUser(int $userId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $groupIds = $data['group_ids'] ?? [];
            
            if (!is_array($groupIds) || empty($groupIds)) {
                return $this->responseFormatter->formatError(
                    'group_ids array is required',
                    Response::HTTP_BAD_REQUEST
                );
            }
            
            $groups = $this->adminUserService->removeGroupsFromUser($userId, $groupIds);
            return $this->responseFormatter->formatSuccess(['groups' => $groups]);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Add roles to user
     * 
     * @route /admin/users/{userId}/roles
     * @method POST
     */
    public function addRolesToUser(int $userId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $roleIds = $data['role_ids'] ?? [];
            
            if (!is_array($roleIds) || empty($roleIds)) {
                return $this->responseFormatter->formatError(
                    'role_ids array is required',
                    Response::HTTP_BAD_REQUEST
                );
            }
            
            $roles = $this->adminUserService->addRolesToUser($userId, $roleIds);
            return $this->responseFormatter->formatSuccess(['roles' => $roles]);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Remove roles from user
     * 
     * @route /admin/users/{userId}/roles
     * @method DELETE
     */
    public function removeRolesFromUser(int $userId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $roleIds = $data['role_ids'] ?? [];
            
            if (!is_array($roleIds) || empty($roleIds)) {
                return $this->responseFormatter->formatError(
                    'role_ids array is required',
                    Response::HTTP_BAD_REQUEST
                );
            }
            
            $roles = $this->adminUserService->removeRolesFromUser($userId, $roleIds);
            return $this->responseFormatter->formatSuccess(['roles' => $roles]);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Send activation mail to user
     * 
     * @route /admin/users/{userId}/send-activation-mail
     * @method POST
     */
    public function sendActivationMail(int $userId): JsonResponse
    {
        try {
            $result = $this->adminUserService->sendActivationMail($userId);
            return $this->responseFormatter->formatSuccess($result);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Clean user data
     * 
     * @route /admin/users/{userId}/clean-data
     * @method POST
     */
    public function cleanUserData(int $userId): JsonResponse
    {
        try {
            $result = $this->adminUserService->cleanUserData($userId);
            return $this->responseFormatter->formatSuccess(['cleaned' => $result]);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Impersonate user
     * 
     * @route /admin/users/{userId}/impersonate
     * @method POST
     */
    public function impersonateUser(int $userId): JsonResponse
    {
        try {
            $result = $this->adminUserService->impersonateUser($userId);
            return $this->responseFormatter->formatSuccess($result);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
} 