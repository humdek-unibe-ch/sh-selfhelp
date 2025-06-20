<?php

namespace App\Controller\Api\V1\Admin;

use App\Service\CMS\Admin\AdminSectionService;
use App\Controller\Trait\RequestValidatorTrait;
use App\Service\Core\ApiResponseFormatter;
use App\Service\JSON\JsonSchemaValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminSectionController extends AbstractController
{
    use RequestValidatorTrait;

    public function __construct(
        private readonly AdminSectionService $adminSectionService,
        private readonly ApiResponseFormatter $apiResponseFormatter,
        private readonly JsonSchemaValidationService $jsonSchemaValidationService
    ) {}

    /**
     * Get a section by ID
     */
    public function getSection(string $page_keyword, int $section_id): Response
    {
        $section = $this->adminSectionService->getSection($page_keyword, $section_id);
        return $this->apiResponseFormatter->formatSuccess(
            $section,
            'responses/admin/section',
            Response::HTTP_OK
        );
    }

    /**
     * Get all children sections for a parent section
     */
    public function getChildrenSections(string $page_keyword, int $parent_section_id): Response
    {
        $children = $this->adminSectionService->getChildrenSections($page_keyword, $parent_section_id);
        return $this->apiResponseFormatter->formatSuccess(
            [
                'parent_section_id' => $parent_section_id,
                'sections' => $children
            ],
            'responses/admin/section_children',
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
            oldParentPageId: $data['oldParentPageId'] ?? null,
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

        $sectionId = $this->adminSectionService->createPageSection(
            $page_keyword,
            $data['styleId'],
            $data['position'] ?? null
        );

        return $this->apiResponseFormatter->formatSuccess(
            ['id' => $sectionId],
            'responses/admin/common/entity_created',
            Response::HTTP_CREATED
        );
    }

    /**
     * Creates a new section with the specified style and adds it as a child to another section
     */
    public function createChildSection(Request $request, string $page_keyword, int $parent_section_id): Response
    {
        $data = $this->validateRequest($request, 'requests/section/create_child_section', $this->jsonSchemaValidationService);

        $sectionId = $this->adminSectionService->createChildSection(
            $page_keyword,
            $parent_section_id,
            $data['styleId'],
            $data['position'] ?? null
        );

        return $this->apiResponseFormatter->formatSuccess(
            ['id' => $sectionId],
            'responses/admin/common/entity_created',
            Response::HTTP_CREATED
        );
    }
}
