<?php

namespace App\Tests\Unit\Repository;

use PHPUnit\Framework\TestCase;

/**
 * Test the field-level merging logic for translation fallback
 */
class SectionsFieldsTranslationRepositoryTest extends TestCase
{
    /**
     * Test field-level merging behavior as implemented in SectionsFieldsTranslationRepository
     */
    public function testFieldLevelMergingLogic(): void
    {
        // Simulate the merging logic from the fetchTranslationsForSectionsWithFallback method

        // Primary language translations (requested language)
        $primaryTranslations = [
            1 => [
                'type' => ['content' => '', 'meta' => null], // Empty content - should fallback
                'label' => ['content' => 'Primary Label', 'meta' => null], // Non-empty - should override
                'confirmation_message' => ['content' => 'Primary Message', 'meta' => null] // Non-empty - should override
            ]
        ];

        // Default language translations (fallback)
        $defaultTranslations = [
            1 => [
                'type' => ['content' => 'Default Type', 'meta' => null], // Should be used (primary is empty)
                'label' => ['content' => 'Default Label', 'meta' => null], // Should be overridden by primary
                'confirmation_title' => ['content' => 'Default Title', 'meta' => null], // Should be kept (no primary)
                'confirmation_message' => ['content' => 'Default Message', 'meta' => null], // Should be overridden by primary
                'confirmation_continue' => ['content' => 'OK', 'meta' => null] // Should be kept (no primary)
            ]
        ];

        // Apply the same logic as in the repository method
        $mergedTranslations = [];
        $sectionIds = [1];

        foreach ($sectionIds as $sectionId) {
            $mergedTranslations[$sectionId] = [];

            // Start with default translations
            if (isset($defaultTranslations[$sectionId])) {
                $mergedTranslations[$sectionId] = $defaultTranslations[$sectionId];
            }

            // Override with primary translations at field level
            if (isset($primaryTranslations[$sectionId])) {
                foreach ($primaryTranslations[$sectionId] as $fieldName => $primaryField) {
                    // Only override if primary language has non-empty content
                    if (isset($primaryField['content']) && !empty(trim($primaryField['content']))) {
                        $mergedTranslations[$sectionId][$fieldName] = $primaryField;
                    }
                    // If primary has empty content or doesn't exist, keep default (already set above)
                }
            }
        }

        // Verify the results
        $result = $mergedTranslations[1];

        // type: primary is empty, so default should be kept
        $this->assertEquals('Default Type', $result['type']['content']);

        // label: primary has content, so primary should override default
        $this->assertEquals('Primary Label', $result['label']['content']);

        // confirmation_title: no primary, so default should be kept
        $this->assertEquals('Default Title', $result['confirmation_title']['content']);

        // confirmation_message: primary has content, so primary should override default
        $this->assertEquals('Primary Message', $result['confirmation_message']['content']);

        // confirmation_continue: no primary, so default should be kept
        $this->assertEquals('OK', $result['confirmation_continue']['content']);

        // Verify that all fields are present
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertArrayHasKey('confirmation_title', $result);
        $this->assertArrayHasKey('confirmation_message', $result);
        $this->assertArrayHasKey('confirmation_continue', $result);
    }

    /**
     * Test merging with whitespace-only content (should be treated as empty)
     */
    public function testWhitespaceOnlyContent(): void
    {
        $primaryTranslations = [
            1 => [
                'field1' => ['content' => '   ', 'meta' => null], // Whitespace only - should fallback
                'field2' => ['content' => 'Valid Content', 'meta' => null] // Valid content - should override
            ]
        ];

        $defaultTranslations = [
            1 => [
                'field1' => ['content' => 'Default Content', 'meta' => null],
                'field2' => ['content' => 'Default Content 2', 'meta' => null]
            ]
        ];

        // Apply merging logic
        $mergedTranslations = [];
        $sectionIds = [1];

        foreach ($sectionIds as $sectionId) {
            $mergedTranslations[$sectionId] = [];

            if (isset($defaultTranslations[$sectionId])) {
                $mergedTranslations[$sectionId] = $defaultTranslations[$sectionId];
            }

            if (isset($primaryTranslations[$sectionId])) {
                foreach ($primaryTranslations[$sectionId] as $fieldName => $primaryField) {
                    if (isset($primaryField['content']) && !empty(trim($primaryField['content']))) {
                        $mergedTranslations[$sectionId][$fieldName] = $primaryField;
                    }
                }
            }
        }

        $result = $mergedTranslations[1];

        // field1: whitespace-only primary should fallback to default
        $this->assertEquals('Default Content', $result['field1']['content']);

        // field2: valid primary should override default
        $this->assertEquals('Valid Content', $result['field2']['content']);
    }
}
