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

    public function addSectionToSection(Request $request, int $parentSectionId): Response
    {
        $data = $this->validateRequest($request, 'requests/section/add_section_to_section', $this->jsonSchemaValidationService);

        $result = $this->adminSectionService->addSectionToSection(
            $parentSectionId,
            $data['childSectionId'],
            $data['position'] ?? null
        );

        return $this->apiResponseFormatter->formatSuccess(
            ['id' => $result->getChildSection()->getId(), 'position' => $result->getPosition()],
            null,
            Response::HTTP_CREATED
        );
    }

    public function updateSectionInSection(Request $request, int $parentSectionId, int $childSectionId): Response
    {
        $data = $this->validateRequest($request, 'requests/section/update_section_in_section', $this->jsonSchemaValidationService);

        $result = $this->adminSectionService->updateSectionInSection(
            $parentSectionId,
            $childSectionId,
            $data['position'] ?? null
        );

        return $this->apiResponseFormatter->formatSuccess(
            ['id' => $result->getChildSection()->getId(), 'position' => $result->getPosition()]
        );
    }

    public function removeSectionFromSection(int $parentSectionId, int $childSectionId): Response
    {
        $this->adminSectionService->removeSectionFromSection($parentSectionId, $childSectionId);

        return $this->apiResponseFormatter->formatSuccess(null, null, Response::HTTP_NO_CONTENT);
    }

    public function deleteSection(int $sectionId): Response
    {
        $this->adminSectionService->deleteSection($sectionId);

        return $this->apiResponseFormatter->formatSuccess(null, null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Creates a new section with the specified style and adds it to a page
     */
    public function createPageSection(Request $request, string $pageKeyword): Response
    {
        $data = $this->validateRequest($request, 'requests/section/create_page_section', $this->jsonSchemaValidationService);

        $result = $this->adminSectionService->createPageSection(
            $pageKeyword,
            $data['styleId'],
            $data['position'] ?? null
        );

        return $this->apiResponseFormatter->formatSuccess(
            ['id' => $result->getId(), 'position' => $result->getPosition()],
            null,
            Response::HTTP_CREATED
        );
    }

    /**
     * Creates a new section with the specified style and adds it as a child to another section
     */
    public function createChildSection(Request $request, int $parentSectionId): Response
    {
        $data = $this->validateRequest($request, 'requests/section/create_child_section', $this->jsonSchemaValidationService);

        $result = $this->adminSectionService->createChildSection(
            $parentSectionId,
            $data['styleId'],
            $data['position'] ?? null
        );

        return $this->apiResponseFormatter->formatSuccess(
            ['id' => $result->getId(), 'position' => $result->getPosition()],
            null,
            Response::HTTP_CREATED
        );
    }
}
