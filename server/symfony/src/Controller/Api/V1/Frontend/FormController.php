<?php

namespace App\Controller\Api\V1\Frontend;

use App\Controller\Trait\RequestValidatorTrait;
use App\Service\CMS\DataService;
use App\Service\CMS\FormValidationService;
use App\Service\Core\ApiResponseFormatter;
use App\Service\JSON\JsonSchemaValidationService;
use App\Service\Auth\UserContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for public form data submissions
 */
class FormController extends AbstractController
{
    use RequestValidatorTrait;

    public function __construct(
        private readonly DataService $dataService,
        private readonly FormValidationService $formValidationService,
        private readonly ApiResponseFormatter $apiResponseFormatter,
        private readonly JsonSchemaValidationService $jsonSchemaValidationService,
        private readonly UserContextService $userContextService,
        private readonly EntityManagerInterface $entityManager
    ) {}

    /**
     * Submit form data
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function submitForm(Request $request): JsonResponse
    {
        try {
            // Validate request schema
            $requestData = $this->validateRequest($request, 'requests/frontend/submit_form', $this->jsonSchemaValidationService);
            
            $pageId = $requestData['page_id'];
            $sectionId = $requestData['section_id'];
            $formData = $requestData['form_data'];

            // Determine if user is authenticated
            $currentUser = $this->userContextService->getCurrentUser();
            $isAuthenticated = $currentUser !== null;

            // Validate form submission
            if ($isAuthenticated) {
                // Authenticated user - full validation
                $validationResult = $this->formValidationService->validateFormSubmission($pageId, $sectionId, $formData);
            } else {
                // Anonymous user - public validation
                $validationResult = $this->formValidationService->validatePublicPageAccess($pageId, $sectionId);
            }

            // Save form data using section ID as table name
            $recordId = $this->dataService->saveData(
                (string) $sectionId,
                $formData,
                'transactionBy_by_user'
            );

            if ($recordId === false) {
                return $this->apiResponseFormatter->formatError(
                    'Failed to save form data',
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            // Prepare response data
            $responseData = [
                'record_id' => $recordId,
                'section_id' => $sectionId,
                'page_id' => $pageId,
                'submitted_at' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
                'user_authenticated' => $isAuthenticated
            ];

            return $this->apiResponseFormatter->formatSuccess(
                $responseData,
                'responses/frontend/form_submitted'
            );

        } catch (\App\Exception\ServiceException $e) {
            return $this->apiResponseFormatter->formatException($e);
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Update form data (for authenticated users only)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function updateForm(Request $request): JsonResponse
    {
        try {
            // Check authentication
            $currentUser = $this->userContextService->getCurrentUser();
            if (!$currentUser) {
                return $this->apiResponseFormatter->formatError(
                    'Authentication required',
                    Response::HTTP_UNAUTHORIZED
                );
            }

            // Validate request schema
            $requestData = $this->validateRequest($request, 'requests/frontend/update_form', $this->jsonSchemaValidationService);
            
            $pageId = $requestData['page_id'];
            $sectionId = $requestData['section_id'];
            $formData = $requestData['form_data'];
            $updateBasedOn = $requestData['update_based_on'] ?? null;

            // Validate form submission
            $validationResult = $this->formValidationService->validateFormSubmission($pageId, $sectionId, $formData);

            // Update form data
            $recordId = $this->dataService->saveData(
                (string) $sectionId,
                $formData,
                'transactionBy_by_user',
                $updateBasedOn,
                true // own entries only
            );

            if ($recordId === false) {
                return $this->apiResponseFormatter->formatError(
                    'Failed to update form data',
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            // Prepare response data
            $responseData = [
                'record_id' => $recordId,
                'section_id' => $sectionId,
                'page_id' => $pageId,
                'updated_at' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM)
            ];

            return $this->apiResponseFormatter->formatSuccess(
                $responseData,
                'responses/frontend/form_updated'
            );

        } catch (\App\Exception\ServiceException $e) {
            return $this->apiResponseFormatter->formatException($e);
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Delete form data (for authenticated users only)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteForm(Request $request): JsonResponse
    {
        try {
            // Check authentication
            $currentUser = $this->userContextService->getCurrentUser();
            if (!$currentUser) {
                return $this->apiResponseFormatter->formatError(
                    'Authentication required',
                    Response::HTTP_UNAUTHORIZED
                );
            }

            // Validate request schema (DELETE with JSON body)
            $requestData = $this->validateRequest($request, 'requests/frontend/delete_form', $this->jsonSchemaValidationService);
            $recordId = (int) $requestData['record_id'];
            $pageId = (int) $requestData['page_id'];
            $sectionId = (int) $requestData['section_id'];

            // Validate ACL delete access and that section belongs to page and is correct type
            $this->formValidationService->validateFormDeletion($pageId, $sectionId);

            // Delete form data
            $success = $this->dataService->deleteData($recordId, true);

            if (!$success) {
                return $this->apiResponseFormatter->formatError(
                    'Failed to delete form data or record not found',
                    Response::HTTP_NOT_FOUND
                );
            }

            return $this->apiResponseFormatter->formatSuccess(
                [
                    'record_id' => $recordId,
                    'section_id' => $sectionId,
                    'page_id' => $pageId,
                ],
                'responses/frontend/form_deleted'
            );

        } catch (\App\Exception\ServiceException $e) {
            return $this->apiResponseFormatter->formatException($e);
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }    
}