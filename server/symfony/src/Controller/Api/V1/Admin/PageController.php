<?php

namespace App\Controller\Api\V1\Admin;

use App\Service\PageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Admin page API controller
 */
#[Route('/api/v1/admin/pages', name: 'api_admin_pages_')]
class PageController extends AbstractController
{
    /**
     * Constructor
     */
    public function __construct(
        private readonly PageService $pageService
    ) {
    }

    /**
     * Get page fields
     * 
     * Retrieves fields for a specific page
     */
    #[Route('/{pageKeyword}/fields', name: 'get_fields', methods: ['GET'])]
    public function getPageFields(string $pageKeyword): JsonResponse
    {
        try {
            $pageFields = $this->pageService->getPageFields($pageKeyword);
            
            return $this->json([
                'status' => Response::HTTP_OK,
                'message' => 'OK',
                'error' => null,
                'logged_in' => true,
                'meta' => [
                    'version' => 'v1',
                    'timestamp' => (new \DateTime())->format('c')
                ],
                'data' => $pageFields
            ]);
        } catch (AccessDeniedException $e) {
            return $this->json([
                'status' => Response::HTTP_FORBIDDEN,
                'message' => 'Forbidden',
                'error' => $e->getMessage(),
                'logged_in' => true,
                'meta' => [
                    'version' => 'v1',
                    'timestamp' => (new \DateTime())->format('c')
                ],
                'data' => null
            ], Response::HTTP_FORBIDDEN);
        } catch (\Exception $e) {
            return $this->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'Bad Request',
                'error' => $e->getMessage(),
                'logged_in' => true,
                'meta' => [
                    'version' => 'v1',
                    'timestamp' => (new \DateTime())->format('c')
                ],
                'data' => null
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get page sections
     * 
     * Retrieves sections for a specific page in a hierarchical structure
     */
    #[Route('/{pageKeyword}/sections', name: 'get_sections', methods: ['GET'])]
    public function getPageSections(string $pageKeyword): JsonResponse
    {
        try {
            $sections = $this->pageService->getPageSections($pageKeyword);
            
            return $this->json([
                'status' => Response::HTTP_OK,
                'message' => 'OK',
                'error' => null,
                'logged_in' => true,
                'meta' => [
                    'version' => 'v1',
                    'timestamp' => (new \DateTime())->format('c')
                ],
                'data' => $sections
            ]);
        } catch (AccessDeniedException $e) {
            return $this->json([
                'status' => Response::HTTP_FORBIDDEN,
                'message' => 'Forbidden',
                'error' => $e->getMessage(),
                'logged_in' => true,
                'meta' => [
                    'version' => 'v1',
                    'timestamp' => (new \DateTime())->format('c')
                ],
                'data' => null
            ], Response::HTTP_FORBIDDEN);
        } catch (\Exception $e) {
            return $this->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'Bad Request',
                'error' => $e->getMessage(),
                'logged_in' => true,
                'meta' => [
                    'version' => 'v1',
                    'timestamp' => (new \DateTime())->format('c')
                ],
                'data' => null
            ], Response::HTTP_BAD_REQUEST);
        }
    }
    
    /**
     * Update page fields
     * 
     * Updates fields for a specific page
     */
    #[Route('/{pageKeyword}/fields', name: 'update_fields', methods: ['PUT'])]
    public function updatePageFields(Request $request, string $pageKeyword): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data || !isset($data['fields'])) {
                throw new \Exception('Invalid request data');
            }
            
            $result = $this->pageService->updatePageFields($pageKeyword, $data['fields']);
            
            return $this->json([
                'status' => Response::HTTP_OK,
                'message' => 'Fields updated successfully',
                'error' => null,
                'logged_in' => true,
                'meta' => [
                    'version' => 'v1',
                    'timestamp' => (new \DateTime())->format('c')
                ],
                'data' => $result
            ]);
        } catch (AccessDeniedException $e) {
            return $this->json([
                'status' => Response::HTTP_FORBIDDEN,
                'message' => 'Forbidden',
                'error' => $e->getMessage(),
                'logged_in' => true,
                'meta' => [
                    'version' => 'v1',
                    'timestamp' => (new \DateTime())->format('c')
                ],
                'data' => null
            ], Response::HTTP_FORBIDDEN);
        } catch (\Exception $e) {
            return $this->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'Bad Request',
                'error' => $e->getMessage(),
                'logged_in' => true,
                'meta' => [
                    'version' => 'v1',
                    'timestamp' => (new \DateTime())->format('c')
                ],
                'data' => null
            ], Response::HTTP_BAD_REQUEST);
        }
    }
    
    /**
     * Add a section to a page
     * 
     * Adds a new section to a specific page
     */
    #[Route('/{pageKeyword}/sections', name: 'add_section', methods: ['POST'])]
    public function addPageSection(Request $request, string $pageKeyword): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data || !isset($data['section_id'])) {
                throw new \Exception('Invalid request data');
            }
            
            $parentId = $data['parent_id'] ?? null;
            $position = $data['position'] ?? null;
            
            $result = $this->pageService->addPageSection(
                $pageKeyword, 
                $data['section_id'], 
                $parentId, 
                $position
            );
            
            return $this->json([
                'status' => Response::HTTP_CREATED,
                'message' => 'Section added successfully',
                'error' => null,
                'logged_in' => true,
                'meta' => [
                    'version' => 'v1',
                    'timestamp' => (new \DateTime())->format('c')
                ],
                'data' => $result
            ], Response::HTTP_CREATED);
        } catch (AccessDeniedException $e) {
            return $this->json([
                'status' => Response::HTTP_FORBIDDEN,
                'message' => 'Forbidden',
                'error' => $e->getMessage(),
                'logged_in' => true,
                'meta' => [
                    'version' => 'v1',
                    'timestamp' => (new \DateTime())->format('c')
                ],
                'data' => null
            ], Response::HTTP_FORBIDDEN);
        } catch (\Exception $e) {
            return $this->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'Bad Request',
                'error' => $e->getMessage(),
                'logged_in' => true,
                'meta' => [
                    'version' => 'v1',
                    'timestamp' => (new \DateTime())->format('c')
                ],
                'data' => null
            ], Response::HTTP_BAD_REQUEST);
        }
    }
    
    /**
     * Remove a section from a page
     * 
     * Removes a section from a specific page
     */
    #[Route('/{pageKeyword}/sections/{sectionId}', name: 'remove_section', methods: ['DELETE'])]
    public function removePageSection(string $pageKeyword, int $sectionId): JsonResponse
    {
        try {
            $result = $this->pageService->removePageSection($pageKeyword, $sectionId);
            
            return $this->json([
                'status' => Response::HTTP_OK,
                'message' => 'Section removed successfully',
                'error' => null,
                'logged_in' => true,
                'meta' => [
                    'version' => 'v1',
                    'timestamp' => (new \DateTime())->format('c')
                ],
                'data' => $result
            ]);
        } catch (AccessDeniedException $e) {
            return $this->json([
                'status' => Response::HTTP_FORBIDDEN,
                'message' => 'Forbidden',
                'error' => $e->getMessage(),
                'logged_in' => true,
                'meta' => [
                    'version' => 'v1',
                    'timestamp' => (new \DateTime())->format('c')
                ],
                'data' => null
            ], Response::HTTP_FORBIDDEN);
        } catch (\Exception $e) {
            return $this->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'Bad Request',
                'error' => $e->getMessage(),
                'logged_in' => true,
                'meta' => [
                    'version' => 'v1',
                    'timestamp' => (new \DateTime())->format('c')
                ],
                'data' => null
            ], Response::HTTP_BAD_REQUEST);
        }
    }
    
    /**
     * Update section order
     * 
     * Updates the order of sections on a specific page
     */
    #[Route('/{pageKeyword}/sections/order', name: 'update_section_order', methods: ['PUT'])]
    public function updateSectionOrder(Request $request, string $pageKeyword): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data || !isset($data['sections'])) {
                throw new \Exception('Invalid request data');
            }
            
            $result = $this->pageService->updateSectionOrder($pageKeyword, $data['sections']);
            
            return $this->json([
                'status' => Response::HTTP_OK,
                'message' => 'Section order updated successfully',
                'error' => null,
                'logged_in' => true,
                'meta' => [
                    'version' => 'v1',
                    'timestamp' => (new \DateTime())->format('c')
                ],
                'data' => $result
            ]);
        } catch (AccessDeniedException $e) {
            return $this->json([
                'status' => Response::HTTP_FORBIDDEN,
                'message' => 'Forbidden',
                'error' => $e->getMessage(),
                'logged_in' => true,
                'meta' => [
                    'version' => 'v1',
                    'timestamp' => (new \DateTime())->format('c')
                ],
                'data' => null
            ], Response::HTTP_FORBIDDEN);
        } catch (\Exception $e) {
            return $this->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'Bad Request',
                'error' => $e->getMessage(),
                'logged_in' => true,
                'meta' => [
                    'version' => 'v1',
                    'timestamp' => (new \DateTime())->format('c')
                ],
                'data' => null
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
