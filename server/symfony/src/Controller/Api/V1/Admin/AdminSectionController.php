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

    public function addSectionToSection(Request $request, int $parent_section_id): Response
    {
        $data = $this->validateRequest($request, 'requests/section/add_section_to_section', $this->jsonSchemaValidationService);

        $result = $this->adminSectionService->addSectionToSection(
            $parent_section_id,
            $data['childSectionId'],
            $data['position'] ?? null
        );

        return $this->apiResponseFormatter->formatSuccess(
            ['id' => $result->getChildSection()->getId(), 'position' => $result->getPosition()],
            null,
            Response::HTTP_CREATED
        );
    }

    public function updateSectionInSection(Request $request, int $parent_section_id, int $child_section_id): Response
    {
        $data = $this->validateRequest($request, 'requests/section/update_section_in_section', $this->jsonSchemaValidationService);

        $result = $this->adminSectionService->updateSectionInSection(
            $parent_section_id,
            $child_section_id,
            $data['position'] ?? null
        );

        return $this->apiResponseFormatter->formatSuccess(
            ['id' => $result->getChildSection()->getId(), 'position' => $result->getPosition()]
        );
    }

    public function removeSectionFromSection(int $parent_section_id, int $child_section_id): Response
    {
        $this->adminSectionService->removeSectionFromSection($parent_section_id, $child_section_id);

        return $this->apiResponseFormatter->formatSuccess(null, null, Response::HTTP_NO_CONTENT);
    }

    public function deleteSection(int $section_id): Response
    {
        $this->adminSectionService->deleteSection($section_id);

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
    public function createChildSection(Request $request, int $parent_section_id): Response
    {
        $data = $this->validateRequest($request, 'requests/section/create_child_section', $this->jsonSchemaValidationService);

        $sectionId = $this->adminSectionService->createChildSection(
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
