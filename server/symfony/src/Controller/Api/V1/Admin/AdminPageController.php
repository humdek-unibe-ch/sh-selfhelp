<?php

namespace App\Controller\Api\V1\Admin;

use App\Controller\Trait\RequestValidatorTrait;
use App\Exception\ServiceException;
use App\Service\CMS\Admin\AdminPageService;
use App\Service\CMS\Frontend\PageService;
use App\Service\Core\ApiResponseFormatter;
use App\Service\Core\LookupService;
use App\Service\JSON\JsonSchemaValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API V1 Admin Controller
 * 
 * Handles admin-related endpoints for API v1
 */
class AdminPageController extends AbstractController
{
    use RequestValidatorTrait;
    
    /**
     * Constructor
     */
    public function __construct(
        private readonly AdminPageService $adminPageService,
        private readonly ApiResponseFormatter $responseFormatter,
        private readonly PageService $pageService,
        private readonly JsonSchemaValidationService $jsonSchemaValidationService
    ) {
    }

    /**
     * Get all pages for admin
     * 
     * @route /admin/pages
     * @method GET
     */
    public function getPages(): JsonResponse
    {
        try {
            // Mode detection logic: default to 'web', could be extended to accept a query param
            $pages = $this->pageService->getAllAccessiblePagesForUser(LookupService::PAGE_ACCESS_TYPES_MOBILE_AND_WEB);            
            return $this->responseFormatter->formatSuccess(
                $pages,
                'responses/common/_acl_page_definition',
                Response::HTTP_OK // Explicitly pass the status code
            );
        } catch (\Throwable $e) {
            // Attempt to get a valid HTTP status code from the exception, default to 500
            $statusCode = (is_int($e->getCode()) && $e->getCode() >= 100 && $e->getCode() <= 599) ? $e->getCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $statusCode
            );
        }
    }

    /**
     * Get page with page fields
     * 
     * @route /admin/pages/{page_keyword}
     * @method GET
     */
    public function getPage(string $page_keyword): JsonResponse
    {
        try {
            $pageWithFields = $this->adminPageService->getPageWithFields($page_keyword);
            
            return $this->responseFormatter->formatSuccess($pageWithFields);
        } catch (ServiceException $e) {
            return $this->responseFormatter->formatException($e);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get page sections
     * 
     * @route /admin/pages/{page_keyword}/sections
     * @method GET
     */
    public function getPageSections(string $page_keyword): JsonResponse
    {
        try {
            $sections = $this->adminPageService->getPageSections($page_keyword);
            return $this->responseFormatter->formatSuccess([
                'page_keyword' => $page_keyword,
                'sections' => $sections
            ], 'responses/admin/page_sections');
        } catch (ServiceException $e) {
            return $this->responseFormatter->formatException($e);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Create a new page
     * 
     * @route /admin/page
     * @method POST
     */
    public function createPage(Request $request): JsonResponse
    {
        try {
            // Validate request against JSON schema
            // This will throw an exception if validation fails
            $data = $this->validateRequest($request, 'requests/admin/create_page', $this->jsonSchemaValidationService);
            
            // Create page using pageAccessTypeCode
            $page = $this->adminPageService->createPage(
                $data['keyword'],
                $data['pageAccessTypeCode'],
                $data['headless'] ?? false,
                $data['openAccess'] ?? false,
                $data['url'] ?? null,
                $data['navPosition'] ?? null,
                $data['footerPosition'] ?? null,
                $data['parent'] ?? null,
            );
            
            // Return success response
            return $this->responseFormatter->formatSuccess(
                $page,
                'responses/admin/page',
                Response::HTTP_CREATED
            );
        } catch (ServiceException $e) {
            return $this->responseFormatter->formatException($e);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Delete page
     * 
     * @route /admin/pages/{page_keyword}
     * @method DELETE
     */
    public function deletePage(string $page_keyword): JsonResponse
    {
        try {
            $page = $this->adminPageService->deletePage($page_keyword);
            return $this->responseFormatter->formatSuccess( 
                $page,
                'responses/admin/page'
            );
        } catch (ServiceException $e) {
            return $this->responseFormatter->formatException($e);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Update a page and its field translations
     * 
     * @Route("/page/{page_keyword}", methods={"PUT"})
     * @param Request $request
     * @param string $page_keyword Page keyword
     * @return JsonResponse
     */
    public function updatePage(Request $request, string $page_keyword): JsonResponse
    {
        try {
            // Validate request against JSON schema
            // This will throw an exception if validation fails
            $data = $this->validateRequest($request, 'requests/admin/update_page', $this->jsonSchemaValidationService);
            
            // Update the page
            $page = $this->adminPageService->updatePage(
                $page_keyword,
                $data['pageData'],
                $data['fields']
            );
            
            // Return updated page with fields
            $pageWithFields = $this->adminPageService->getPageWithFields($page_keyword);
            
            return $this->responseFormatter->formatSuccess($pageWithFields);
        } catch (ServiceException $e) {
            return $this->responseFormatter->formatException($e);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function addSectionToPage(Request $request, string $page_keyword): Response
    {
        $data = $this->validateRequest($request, 'requests/page/add_section_to_page', $this->jsonSchemaValidationService);

        $result = $this->adminPageService->addSectionToPage(
            $page_keyword,
            $data['sectionId'],
            $data['position'],
            $data['oldParentSectionId'] ?? null,
        );

        return $this->responseFormatter->formatSuccess(
            ['id' => $result->getSection()->getId(), 'position' => $result->getPosition()],
            null,
            Response::HTTP_OK
        );
    }

    public function removeSectionFromPage(string $page_keyword, int $section_id): Response
    {
        $this->adminPageService->removeSectionFromPage($page_keyword, $section_id);
        return $this->responseFormatter->formatSuccess(null, null, Response::HTTP_NO_CONTENT);
    }
}
