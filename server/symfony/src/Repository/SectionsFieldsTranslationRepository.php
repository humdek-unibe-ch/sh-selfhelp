<?php

namespace App\Repository;

use App\Entity\SectionsFieldsTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SectionsFieldsTranslation>
 */
class SectionsFieldsTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SectionsFieldsTranslation::class);
    }

    /**
     * Fetch all section field translations for a list of section IDs and specific language
     *
     * @param array $sectionIds Array of section IDs
     * @param int $languageId Language ID
     * @return array Associative array with section_id as key and translations as values
     */
    public function fetchTranslationsForSections(array $sectionIds, int $languageId): array
    {
        if (empty($sectionIds)) {
            return [];
        }

        $qb = $this->createQueryBuilder('sft')
            ->select('s.id AS section_id, f.id AS field_id, f.name AS field_name, l.locale AS locale, sft.content, sft.meta')
            ->leftJoin('sft.section', 's')
            ->leftJoin('sft.field', 'f')
            ->leftJoin('sft.language', 'l')
            ->where('s.id IN (:sectionIds)')
            ->andWhere('l.id = :languageId')
            ->setParameter('sectionIds', $sectionIds)
            ->setParameter('languageId', $languageId);

        $results = $qb->getQuery()->getResult();
        
        // Organize results by section_id
        $translations = [];
        foreach ($results as $result) {
            $sectionId = $result['section_id'];
            if (!isset($translations[$sectionId])) {
                $translations[$sectionId] = [];
            }
            
            $translations[$sectionId][$result['field_name']] = [
                'content' => $result['content'],
                'meta' => $result['meta']
            ];
        }
        
        return $translations;
    }
}
