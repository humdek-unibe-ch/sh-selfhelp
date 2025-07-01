<?php

namespace App\Repository;

use App\Entity\PagesFieldsTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PagesFieldsTranslation>
 */
class PagesFieldsTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PagesFieldsTranslation::class);
    }

    /**
     * Fetch all page field translations for a list of page IDs and specific language
     * Only fetches translations for fields with display=1 (title fields)
     *
     * @param array $pageIds Array of page IDs
     * @param int $languageId Language ID
     * @return array Associative array with page_id as key and translations as values
     */
    public function fetchTitleTranslationsForPages(array $pageIds, int $languageId): array
    {
        if (empty($pageIds)) {
            return [];
        }

        $qb = $this->createQueryBuilder('pft')
            ->select('p.id AS page_id, f.id AS field_id, f.name AS field_name, pft.content')
            ->leftJoin('pft.page', 'p')
            ->leftJoin('pft.field', 'f')
            ->leftJoin('pft.language', 'l')
            ->where('p.id IN (:pageIds)')
            ->andWhere('l.id = :languageId')
            ->andWhere('f.display = true') // Only display fields (title fields)
            ->setParameter('pageIds', $pageIds)
            ->setParameter('languageId', $languageId);

        $results = $qb->getQuery()->getResult();
        
        // Organize results by page_id
        $translations = [];
        foreach ($results as $result) {
            $pageId = $result['page_id'];
            if (!isset($translations[$pageId])) {
                $translations[$pageId] = [];
            }
            
            $translations[$pageId][$result['field_name']] = $result['content'];
        }
        
        return $translations;
    }

    /**
     * Fetch page field translations with fallback to default language
     * Only fetches translations for fields with display=1 (title fields)
     *
     * @param array $pageIds Array of page IDs
     * @param int $languageId Primary language ID
     * @param int|null $defaultLanguageId Default language ID for fallback
     * @return array Associative array with page_id as key and translations as values
     */
    public function fetchTitleTranslationsWithFallback(array $pageIds, int $languageId, ?int $defaultLanguageId = null): array
    {
        if (empty($pageIds)) {
            return [];
        }

        // Get primary language translations
        $primaryTranslations = $this->fetchTitleTranslationsForPages($pageIds, $languageId);
        
        // If no default language or it's the same as primary, return primary translations
        if ($defaultLanguageId === null || $defaultLanguageId === $languageId) {
            return $primaryTranslations;
        }
        
        // Get default language translations for fallback
        $defaultTranslations = $this->fetchTitleTranslationsForPages($pageIds, $defaultLanguageId);
        
        // Merge translations with primary taking precedence
        $mergedTranslations = [];
        foreach ($pageIds as $pageId) {
            $mergedTranslations[$pageId] = [];
            
            // Start with default translations
            if (isset($defaultTranslations[$pageId])) {
                $mergedTranslations[$pageId] = $defaultTranslations[$pageId];
            }
            
            // Override with primary translations
            if (isset($primaryTranslations[$pageId])) {
                $mergedTranslations[$pageId] = array_merge($mergedTranslations[$pageId], $primaryTranslations[$pageId]);
            }
        }
        
        return $mergedTranslations;
    }
} 