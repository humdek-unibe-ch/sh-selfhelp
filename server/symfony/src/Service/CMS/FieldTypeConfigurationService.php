<?php

namespace App\Service\CMS;

use App\Service\Cache\Core\CacheService;
use App\Service\ACL\ACLService;
use App\Service\Auth\UserContextService;
use App\Repository\PageRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Global service for managing field type configurations
 * Provides centralized access to field configurations stored in the database
 */
class FieldTypeConfigurationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheService $cache,
        private readonly ACLService $aclService,
        private readonly UserContextService $userContextService,
        private readonly PageRepository $pageRepository
    ) {
    }

    /**
     * Get field configuration for a specific field type
     *
     * @param string $fieldType The field type name
     * @param bool $includeOptions Whether to include dynamic options
     * @return array The field configuration
     */
    public function getFieldConfig(string $fieldType, bool $includeOptions = true): array
    {
        // Get base configuration from database
        $config = $this->getFieldTypeConfigFromDatabase($fieldType);

        // Add dynamic options if requested
        if ($includeOptions) {
            $config = $this->addDynamicOptions($fieldType, $config);
        }

        return $config;
    }

    /**
     * Get all available field type configurations
     *
     * @return array Array of field type configurations indexed by field type name
     */
    public function getAllFieldTypeConfigs(): array
    {
        $cacheKey = "all_field_type_configs";

        return $this->cache
            ->withCategory(CacheService::CATEGORY_FIELD_TYPES)
            ->getItem(
                $cacheKey,
                function () {
                    $qb = $this->entityManager->createQueryBuilder();
                    $qb->select('f.name as field_name, f.config, ft.name as type_name')
                        ->from('App\Entity\Field', 'f')
                        ->leftJoin('f.type', 'ft')
                        ->where('f.name LIKE :templatePattern')
                        ->setParameter('templatePattern', 'template_%');

                    $results = $qb->getQuery()->getResult();

                    $configs = [];
                    foreach ($results as $result) {
                        $fieldType = str_replace('template_', '', $result['field_name']);
                        if ($result['config']) {
                            $configs[$fieldType] = json_decode($result['config'], true) ?? [];
                        }
                    }

                    // Add defaults for field types without templates
                    $configs = array_merge($this->getDefaultConfigs(), $configs);

                    return $configs;
                }
            );
    }

    /**
     * Get field type configuration from database template
     *
     * @param string $fieldType The field type name
     * @return array The field configuration from database
     */
    private function getFieldTypeConfigFromDatabase(string $fieldType): array
    {
        $cacheKey = "field_type_config_{$fieldType}";

        return $this->cache
            ->withCategory(CacheService::CATEGORY_FIELD_TYPES)
            ->getItem(
                $cacheKey,
                function () use ($fieldType) {
                    $qb = $this->entityManager->createQueryBuilder();
                    $qb->select('f.config')
                        ->from('App\Entity\Field', 'f')
                        ->leftJoin('f.type', 'ft')
                        ->where('ft.name = :fieldType')
                        ->andWhere('f.name LIKE :templatePattern')
                        ->setParameter('fieldType', $fieldType)
                        ->setParameter('templatePattern', 'template_%')
                        ->setMaxResults(1);

                    $result = $qb->getQuery()->getOneOrNullResult();

                    if ($result && $result['config']) {
                        return json_decode($result['config'], true) ?? [];
                    }

                    // Fallback to defaults
                    return $this->getDefaultConfigForFieldType($fieldType);
                }
            );
    }

    /**
     * Add dynamic options to configuration based on field type
     *
     * @param string $fieldType The field type name
     * @param array $config The base configuration
     * @return array Configuration with dynamic options added
     */
    private function addDynamicOptions(string $fieldType, array $config): array
    {
        switch ($fieldType) {
            case 'select-group':
                $config['options'] = $this->getGroups();
                break;
            case 'select-data_table':
                $config['options'] = $this->getDataTables();
                break;
            case 'select-page-keyword':
                $config['options'] = $this->getPageKeywords();
                break;
            case 'select-css':
                $config['options'] = []; // Options loaded dynamically via API
                break;
        }

        return $config;
    }

    /**
     * Get default configurations for field types
     *
     * @return array Default configurations
     */
    private function getDefaultConfigs(): array
    {
        return [
            'select-group' => [
                'multiSelect' => true,
                'creatable' => false,
                'separator' => ',',
                'apiUrl' => null
            ],
            'select-data_table' => [
                'multiSelect' => false,
                'creatable' => false,
                'separator' => ',',
                'apiUrl' => null
            ],
            'select-page-keyword' => [
                'multiSelect' => false,
                'creatable' => false,
                'separator' => ',',
                'apiUrl' => null
            ],
            'select-css' => [
                'multiSelect' => true,
                'creatable' => true,
                'separator' => ' ',
                'apiUrl' => '/cms-api/v1/frontend/css-classes'
            ]
        ];
    }

    /**
     * Get default configuration for a specific field type
     *
     * @param string $fieldType The field type name
     * @return array Default configuration
     */
    private function getDefaultConfigForFieldType(string $fieldType): array
    {
        $defaults = $this->getDefaultConfigs();
        return $defaults[$fieldType] ?? [];
    }

    /**
     * Get groups for select-group field type
     *
     * @return array The groups formatted as options
     */
    private function getGroups(): array
    {
        $cacheKey = "groups";

        return $this->cache
            ->withCategory(CacheService::CATEGORY_GROUPS)
            ->getList(
                $cacheKey,
                function () {
                    $qb = $this->entityManager->createQueryBuilder();
                    $qb->select('g.id, g.name')
                        ->from('App\Entity\Group', 'g')
                        ->orderBy('g.name', 'ASC');

                    $groups = $qb->getQuery()->getResult();

                    return array_map(fn($group) => [
                        'value' => (string) $group['id'],
                        'text' => $group['name']
                    ], $groups);
                }
            );
    }

    /**
     * Get data tables for select-data_table field type
     *
     * @return array The data tables formatted as options
     */
    private function getDataTables(): array
    {
        $cacheKey = "data_tables";
        return $this->cache
            ->withCategory(CacheService::CATEGORY_DATA_TABLES)
            ->getList(
                $cacheKey,
                function () {
                    $qb = $this->entityManager->createQueryBuilder();
                    $qb->select('dt.id, dt.name')
                        ->from('App\Entity\DataTable', 'dt')
                        ->orderBy('dt.name', 'ASC');

                    $dataTables = $qb->getQuery()->getResult();

                    return array_map(fn($table) => [
                        'value' => (string) $table['id'],
                        'text' => $table['name']
                    ], $dataTables);
                }
            );
    }

    /**
     * Get page keywords for select-page-keyword field type
     *
     * @return array The page keywords formatted as options
     */
    private function getPageKeywords(): array
    {
        $cacheKey = "page_keywords";
        return $this->cache
            ->withCategory(CacheService::CATEGORY_PAGES)
            ->getList(
                $cacheKey,
                function () {
                    $qb = $this->entityManager->createQueryBuilder();
                    $qb->select('p.id, p.keyword')
                        ->from('App\Entity\Page', 'p')
                        ->where('p.keyword IS NOT NULL')
                        ->orderBy('p.keyword', 'ASC');

                    $pages = $qb->getQuery()->getResult();

                    return array_map(fn($page) => [
                        'value' => (string) $page['id'],
                        'text' => $page['keyword']
                    ], $pages);
                }
            );
    }

    /**
     * Invalidate field type configuration cache
     * Call this when field configurations are updated
     */
    public function invalidateFieldTypeCache(): void
    {
        $this->cache
            ->withCategory(CacheService::CATEGORY_FIELD_TYPES)
            ->invalidateCategory();
    }
}
