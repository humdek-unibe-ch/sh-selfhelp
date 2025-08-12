<?php

namespace App\Controller\Api\V1\Admin;

use App\Controller\Trait\RequestValidatorTrait;
use App\Exception\ServiceException;
use App\Service\CMS\Admin\AdminActionService;
use App\Service\Core\ApiResponseFormatter;
use App\Service\JSON\JsonSchemaValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminActionController extends AbstractController
{
    use RequestValidatorTrait;

    public function __construct(
        private readonly AdminActionService $adminActionService,
        private readonly ApiResponseFormatter $responseFormatter,
        private readonly JsonSchemaValidationService $jsonSchemaValidationService
    ) {
    }

    /**
     * Create action
     * @route /admin/actions
     * @method POST
     */
    public function createAction(Request $request): JsonResponse
    {
        try {
            $data = $this->validateRequest($request, 'requests/admin/create_action', $this->jsonSchemaValidationService);

            $result = $this->adminActionService->createAction((array) $data);

            return $this->responseFormatter->formatSuccess(
                $result,
                'responses/admin/actions/action_envelope',
                Response::HTTP_CREATED
            );
        } catch (\Throwable $e) {
            $message = $e instanceof ServiceException ? $e->getMessage() : 'Internal Server Error';
            $status = $e instanceof ServiceException ? $e->getCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
            return $this->responseFormatter->formatError($message, $status);
        }
    }

    /**
     * Get actions with pagination
     * @route /admin/actions
     * @method GET
     */
    public function getActions(Request $request): JsonResponse
    {
        try {
            $page = (int) $request->query->get('page', 1);
            $pageSize = (int) $request->query->get('pageSize', 20);
            $search = $request->query->get('search');
            $sort = $request->query->get('sort');
            $sortDirection = $request->query->get('sortDirection', 'asc');

            $result = $this->adminActionService->getActions($page, $pageSize, $search, $sort, $sortDirection);

            return $this->responseFormatter->formatSuccess(
                $result,
                'responses/admin/actions/actions_envelope'
            );
        } catch (\Throwable $e) {
            $message = $e instanceof ServiceException ? $e->getMessage() : 'Internal Server Error';
            $status = $e instanceof ServiceException ? $e->getCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
            return $this->responseFormatter->formatError($message, $status);
        }
    }

    /**
     * Update action
     * @route /admin/actions/{actionId}
     * @method PUT
     */
    public function updateAction(Request $request, int $actionId): JsonResponse
    {
        try {
            $data = $this->validateRequest($request, 'requests/admin/update_action', $this->jsonSchemaValidationService);

            $result = $this->adminActionService->updateAction($actionId, (array) $data);

            return $this->responseFormatter->formatSuccess(
                $result,
                'responses/admin/actions/action_envelope'
            );
        } catch (\Throwable $e) {
            $message = $e instanceof ServiceException ? $e->getMessage() : 'Internal Server Error';
            $status = $e instanceof ServiceException ? $e->getCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
            return $this->responseFormatter->formatError($message, $status);
        }
    }

    /**
     * Delete action
     * @route /admin/actions/{actionId}
     * @method DELETE
     */
    public function deleteAction(int $actionId): JsonResponse
    {
        try {
            $this->adminActionService->deleteAction($actionId);
            return $this->responseFormatter->formatSuccess(['deleted' => true], 'responses/admin/actions/action_deleted_envelope');
        } catch (\Throwable $e) {
            $message = $e instanceof ServiceException ? $e->getMessage() : 'Internal Server Error';
            $status = $e instanceof ServiceException ? $e->getCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
            return $this->responseFormatter->formatError($message, $status);
        }
    }

    /**
     * Get single action by ID
     * @route /admin/actions/{actionId}
     * @method GET
     */
    public function getActionById(int $actionId): JsonResponse
    {
        try {
            $result = $this->adminActionService->getActionById($actionId);
            return $this->responseFormatter->formatSuccess(
                $result,
                'responses/admin/actions/action_envelope'
            );
        } catch (\Throwable $e) {
            $message = $e instanceof ServiceException ? $e->getMessage() : 'Internal Server Error';
            $status = $e instanceof ServiceException ? $e->getCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
            return $this->responseFormatter->formatError($message, $status);
        }
    }
}


