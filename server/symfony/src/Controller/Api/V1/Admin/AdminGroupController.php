<?php

namespace App\Controller\Api\V1\Admin;

use App\Controller\Trait\RequestValidatorTrait;
use App\Service\CMS\Admin\AdminGroupService;
use App\Service\Core\ApiResponseFormatter;
use App\Service\JSON\JsonSchemaValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Admin Group Controller
 * 
 * Handles all group management operations for admin interface
 */
class AdminGroupController extends AbstractController
{
    use RequestValidatorTrait;

    public function __construct(
        private readonly AdminGroupService $adminGroupService,
        private readonly ApiResponseFormatter $responseFormatter,
        private readonly JsonSchemaValidationService $jsonSchemaValidationService
    ) {
    }

    /**
     * Get groups with pagination, search, and sorting
     * 
     * @param page: which page of results (default: 1)
     * @param pageSize: how many groups per page (default: 20, max: 100)
     * @param search: search term for name or description
     * @param sort: sort field (name, description, requires_2fa)
     * @param sortDirection: asc or desc (default: asc)
     */
    #[Route('/cms-api/v1/admin/groups', name: 'admin_groups_list', methods: ['GET'])]
    public function getGroups(Request $request): JsonResponse
    {
        try {
            $page = (int)$request->query->get('page', 1);
            $pageSize = (int)$request->query->get('pageSize', 20);
            $search = $request->query->get('search');
            $sort = $request->query->get('sort');
            $sortDirection = $request->query->get('sortDirection', 'asc');

            $result = $this->adminGroupService->getGroups($page, $pageSize, $search, $sort, $sortDirection);

            return $this->responseFormatter->formatSuccess($result);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get single group by ID with ACLs
     * 
     * @route /admin/groups/{groupId}
     * @method GET
     */
    public function getGroupById(int $groupId): JsonResponse
    {
        try {
            $group = $this->adminGroupService->getGroupById($groupId);
            return $this->responseFormatter->formatSuccess($group);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Create new group
     * 
     * @route /admin/groups
     * @method POST
     */
    public function createGroup(Request $request): JsonResponse
    {
        try {
            $data = $this->validateRequest($request, 'requests/admin/create_group', $this->jsonSchemaValidationService);
            
            $group = $this->adminGroupService->createGroup($data);
            
            return $this->responseFormatter->formatSuccess(
                $group,
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
     * Update existing group
     * 
     * @route /admin/groups/{groupId}
     * @method PUT
     */
    public function updateGroup(int $groupId, Request $request): JsonResponse
    {
        try {
            $data = $this->validateRequest($request, 'requests/admin/update_group', $this->jsonSchemaValidationService);
            
            $group = $this->adminGroupService->updateGroup($groupId, $data);
            // Group cache is automatically invalidated by the service
            
            return $this->responseFormatter->formatSuccess($group);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Delete group
     * 
     * @route /admin/groups/{groupId}
     * @method DELETE
     */
    public function deleteGroup(int $groupId): JsonResponse
    {
        try {
            $this->adminGroupService->deleteGroup($groupId);
            
            // Group cache is automatically invalidated by the service
            
            return $this->responseFormatter->formatSuccess(['deleted' => true]);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get group ACLs
     * 
     * @route /admin/groups/{groupId}/acls
     * @method GET
     */
    public function getGroupAcls(int $groupId): JsonResponse
    {
        try {
            $acls = $this->adminGroupService->getGroupAcls($groupId);
            return $this->responseFormatter->formatSuccess(['acls' => $acls]);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Update group ACLs (bulk update)
     * 
     * @route /admin/groups/{groupId}/acls
     * @method PUT
     */
    public function updateGroupAcls(int $groupId, Request $request): JsonResponse
    {
        try {
            $data = $this->validateRequest($request, 'requests/admin/update_group_acls', $this->jsonSchemaValidationService);
            
            $acls = $this->adminGroupService->updateGroupAcls($groupId, $data['acls']);
            
            // Permissions cache is automatically invalidated by the service
            
            return $this->responseFormatter->formatSuccess(['acls' => $acls]);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
} 