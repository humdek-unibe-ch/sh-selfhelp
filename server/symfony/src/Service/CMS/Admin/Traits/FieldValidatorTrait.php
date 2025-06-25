<?php

namespace App\Service\CMS\Admin\Traits;

use App\Exception\ServiceException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait for validating fields in admin services
 */
trait FieldValidatorTrait
{
    /**
     * Validate that fields belong to a page type
     * 
     * @param array $fieldIds Array of field IDs to validate
     * @param int $pageTypeId The page type ID
     * @param EntityManagerInterface $entityManager
     * @throws ServiceException If any fields don't belong to the page type
     */
    protected function validatePageTypeFields(array $fieldIds, int $pageTypeId, EntityManagerInterface $entityManager): void
    {
        if (empty($fieldIds)) {
            return;
        }

        // Get all valid field IDs for this page type
        $validFieldIds = $entityManager->getRepository(\App\Entity\PageTypeField::class)
            ->createQueryBuilder('ptf')
            ->select('ptf.idFields')
            ->where('ptf.idPageType = :pageTypeId')
            ->andWhere('ptf.idFields IN (:fieldIds)')
            ->setParameter('pageTypeId', $pageTypeId)
            ->setParameter('fieldIds', $fieldIds)
            ->getQuery()
            ->getScalarResult();

        $validFieldIds = array_column($validFieldIds, 'idFields');
        $invalidFieldIds = array_diff($fieldIds, $validFieldIds);

        if (!empty($invalidFieldIds)) {
            throw new ServiceException(
                sprintf(
                    "Fields [%s] do not belong to page type ID %d",
                    implode(', ', $invalidFieldIds),
                    $pageTypeId
                ),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Validate that fields belong to a style
     * 
     * @param array $fieldIds Array of field IDs to validate
     * @param int $styleId The style ID
     * @param EntityManagerInterface $entityManager
     * @throws ServiceException If any fields don't belong to the style
     */
    protected function validateStyleFields(array $fieldIds, int $styleId, EntityManagerInterface $entityManager): void
    {
        if (empty($fieldIds)) {
            return;
        }

        $validFieldIds = $entityManager->getRepository(\App\Entity\StylesField::class)
            ->createQueryBuilder('sf')
            ->select('IDENTITY(sf.field)')
            ->where('sf.style = :styleId')
            ->andWhere('sf.field IN (:fieldIds)')
            ->setParameter('styleId', $styleId)
            ->setParameter('fieldIds', $fieldIds)
            ->getQuery()
            ->getScalarResult();
        
        $validFieldIds = array_column($validFieldIds, 1); // Extract field IDs from result
        $invalidFieldIds = array_diff($fieldIds, $validFieldIds);
        
        if (!empty($invalidFieldIds)) {
            throw new ServiceException(
                sprintf("Fields [%s] do not belong to style %d", 
                    implode(', ', $invalidFieldIds), 
                    $styleId
                ),
                Response::HTTP_BAD_REQUEST
            );
        }
    }
} 