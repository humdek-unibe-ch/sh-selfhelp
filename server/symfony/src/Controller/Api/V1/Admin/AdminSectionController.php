<?php

namespace App\Controller\Api\V1\Admin;

use App\Service\CMS\Admin\AdminSectionService;
use App\Controller\Trait\RequestValidatorTrait;
use App\Service\Core\ApiResponseFormatter;
use App\Service\JSON\JsonSchemaValidationService;
use App\Service\CMS\Common\SectionUtilityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminSectionController extends AbstractController
{
    use RequestValidatorTrait;

    public function __construct(
        private readonly AdminSectionService $adminSectionService,
        private readonly ApiResponseFormatter $apiResponseFormatter,
        private readonly JsonSchemaValidationService $jsonSchemaValidationService,
        private readonly SectionUtilityService $sectionUtilityService
    ) {}

    /**
     * Get a section by ID
     */
    public function getSection(string $page_keyword, int $section_id): Response
    {
        $section = $this->adminSectionService->getSection($page_keyword, $section_id);
        return $this->apiResponseFormatter->formatSuccess(
            [
                'section' => $section['section'] ?? $section,
                'fields' => $section['fields'] ?? [],
                'languages' => $section['languages'] ?? [],
            ],
            'responses/admin/sections/section',
            Response::HTTP_OK
        );
    }
    public function addSectionToSection(Request $request, string $page_keyword, int $parent_section_id): Response
    {
        $data = $this->validateRequest($request, 'requests/section/add_section_to_section', $this->jsonSchemaValidationService);

        $result = $this->adminSectionService->addSectionToSection(
            page_keyword: $page_keyword,
            parent_section_id: $parent_section_id,
            child_section_id: $data['childSectionId'],
            position: $data['position'],
            oldParentPageKeyword: $data['oldParentPageId'] ?? null,
            oldParentSectionId: $data['oldParentSectionId'] ?? null
        );

        return $this->apiResponseFormatter->formatSuccess(
            ['id' => $result->getChildSection()->getId(), 'position' => $result->getPosition()],
            null,
            Response::HTTP_OK
        );
    }

    public function removeSectionFromSection(string $page_keyword, int $parent_section_id, int $child_section_id): Response
    {
        $this->adminSectionService->removeSectionFromSection($page_keyword, $parent_section_id, $child_section_id);

        return $this->apiResponseFormatter->formatSuccess(null, null, Response::HTTP_NO_CONTENT);
    }

    public function deleteSection(string $page_keyword, int $section_id): Response
    {
        $this->adminSectionService->deleteSection($page_keyword, $section_id);

        return $this->apiResponseFormatter->formatSuccess(null, null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Creates a new section with the specified style and adds it to a page
     */
    public function createPageSection(Request $request, string $page_keyword): Response
    {
        $data = $this->validateRequest($request, 'requests/section/create_page_section', $this->jsonSchemaValidationService);

        $result = $this->adminSectionService->createPageSection(
            $page_keyword,
            $data['styleId'],
            $data['position'] ?? null
        );

        return $this->apiResponseFormatter->formatSuccess(
            [
                'id' => $result['id'],
                'position' => $result['position'],
            ],
            null,
            Response::HTTP_CREATED
        );
    }

    /**
     * Creates a new section with the specified style and adds it as a child to another section
     */
    public function createChildSection(Request $request, string $page_keyword, int $parent_section_id): Response
    {
        $data = $this->validateRequest($request, 'requests/section/create_child_section', $this->jsonSchemaValidationService);

        $result = $this->adminSectionService->createChildSection(
            $page_keyword,
            $parent_section_id,
            $data['styleId'],
            $data['position'] ?? null
        );

        return $this->apiResponseFormatter->formatSuccess(
            [
                'id' => $result['id'],
                'position' => $result['position'],
            ],
            null,
            Response::HTTP_CREATED
        );
    }

    /**
     * Update a section
     */
    public function updateSection(Request $request, string $page_keyword, int $section_id): Response
    {
        try {
            // Validate request against JSON schema
            $data = $this->validateRequest($request, 'requests/section/update_section', $this->jsonSchemaValidationService);
            
            // Update the section
            $section = $this->adminSectionService->updateSection(
                $page_keyword,
                $section_id,
                isset($data['sectionName']) ? $data['sectionName'] : null,
                $data['contentFields'],
                $data['propertyFields']
            );
            
            // Return updated section with fields
            $sectionWithFields = $this->adminSectionService->getSection($page_keyword, $section->getId());
            
            return $this->apiResponseFormatter->formatSuccess(
                [
                    'section' => $sectionWithFields['section'],
                    'fields' => $sectionWithFields['fields'],
                    'languages' => $sectionWithFields['languages'],
                ],
                'responses/admin/sections/section',
                Response::HTTP_OK
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
     * Export all sections of a given page (including all nested sections) as JSON
     * 
     * @param string $page_keyword The keyword of the page to export sections from
     * @return Response JSON response with all page sections
     */
    public function exportPageSections(string $page_keyword): Response
    {
        try {
            $sectionsData = $this->adminSectionService->exportPageSections($page_keyword);
            
            return $this->apiResponseFormatter->formatSuccess(
                ['sectionsData' => $sectionsData],
                null,
                Response::HTTP_OK
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
     * Export a selected section (and all of its nested children) as JSON
     * 
     * @param string $page_keyword The keyword of the page containing the section
     * @param int $section_id The ID of the section to export
     * @return Response JSON response with the section and its children
     */
    public function exportSection(string $page_keyword, int $section_id): Response
    {
        try {
            $sectionData = $this->adminSectionService->exportSection($page_keyword, $section_id);
            
            return $this->apiResponseFormatter->formatSuccess(
                ['sectionsData' => $sectionData],
                null,
                Response::HTTP_OK
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
     * Import sections from JSON into a target page
     * 
     * @param Request $request The request containing the sections JSON data
     * @param string $page_keyword The keyword of the target page
     * @return Response JSON response with the result of the import operation
     */
    public function importSectionsToPage(Request $request, string $page_keyword): Response
    {
        try {
            // Validate request against JSON schema
            $data = $this->validateRequest($request, 'requests/section/import_sections', $this->jsonSchemaValidationService);
            
            // Import the sections
            $result = $this->adminSectionService->importSectionsToPage(
                $page_keyword, 
                $data['sections'], 
                $data['position'] ?? null
            );
            
            return $this->apiResponseFormatter->formatSuccess(
                ['importedSections' => $result],
                null,
                Response::HTTP_OK
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
     * Import sections from JSON into a specific section
     * 
     * @param Request $request The request containing the sections JSON data
     * @param string $page_keyword The keyword of the target page
     * @param int $parent_section_id The ID of the parent section to import into
     * @return Response JSON response with the result of the import operation
     */
    public function importSectionsToSection(Request $request, string $page_keyword, int $parent_section_id): Response
    {
        try {
            // Validate request against JSON schema
            $data = $this->validateRequest($request, 'requests/section/import_sections', $this->jsonSchemaValidationService);
            
            // Import the sections
            $result = $this->adminSectionService->importSectionsToSection(
                $page_keyword, 
                $parent_section_id, 
                $data['sections'], 
                $data['position'] ?? null
            );
            
            return $this->apiResponseFormatter->formatSuccess(
                ['importedSections' => $result],
                null,
                Response::HTTP_OK
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
