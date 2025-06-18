<?php

namespace App\Tests\Service\CMS\Admin;

use App\Entity\Field;
use App\Entity\FieldType;
use App\Entity\Gender;
use App\Entity\Language;
use App\Entity\Section;
use App\Entity\Style;
use App\Entity\StylesField;
use App\Entity\SectionsFieldsTranslation;
use App\Exception\ServiceException;
use App\Repository\PageRepository;
use App\Repository\SectionRepository;
use App\Repository\StyleRepository;
use App\Service\ACL\ACLService;
use App\Service\CMS\Admin\AdminSectionService;
use App\Service\Core\TransactionService;
use App\Service\Auth\UserContextService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

class AdminSectionServiceTest extends TestCase
{
    private $entityManager;
    private $sectionRepository;
    private $transactionService;
    private $styleRepository;
    private $pageRepository;
    private $aclService;
    private $userContextService;
    private $adminSectionService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->sectionRepository = $this->createMock(SectionRepository::class);
        $this->transactionService = $this->createMock(TransactionService::class);
        $this->styleRepository = $this->createMock(StyleRepository::class);
        $this->pageRepository = $this->createMock(PageRepository::class);
        $this->aclService = $this->createMock(ACLService::class);
        $this->userContextService = $this->createMock(UserContextService::class);

        $this->adminSectionService = new AdminSectionService(
            $this->entityManager,
            $this->sectionRepository,
            $this->transactionService,
            $this->styleRepository,
            $this->pageRepository,
            $this->aclService,
            $this->userContextService
        );
    }

    /**
     * Test getting a section that doesn't exist
     */
    public function testGetSectionNotFound(): void
    {
        // Configure mocks
        $this->sectionRepository
            ->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        // Assert exception is thrown
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Section not found');

        // Call the method
        $this->adminSectionService->getSection(999);
    }

    /**
     * Test getting a section with no permission
     */
    public function testGetSectionNoPermission(): void
    {
        // Create mock section
        $sectionId = 123;
        $section = $this->createMock(Section::class);
        $section->method('getId')->willReturn($sectionId);

        // Configure mocks
        $this->sectionRepository
            ->expects($this->once())
            ->method('find')
            ->with($sectionId)
            ->willReturn($section);

        $this->aclService
            ->expects($this->once())
            ->method('hasAccess')
            ->with($sectionId, 'select')
            ->willReturn(false);

        // Assert exception is thrown
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Access denied to section');

        // Call the method
        $this->adminSectionService->getSection(123);
    }

    /**
     * Test getting a section successfully
     */
    public function testGetSectionSuccess(): void
    {
        // Create mock entities
        $sectionId = 123;
        $styleId = 456;

        $section = $this->createMock(Section::class);
        $section->method('getId')->willReturn($sectionId);
        
        // Mock field types
        $fieldType1 = $this->createMock(FieldType::class);
        $fieldType1->method('getId')->willReturn(10);
        $fieldType1->method('getName')->willReturn('text');
        
        $fieldType2 = $this->createMock(FieldType::class);
        $fieldType2->method('getId')->willReturn(11);
        $fieldType2->method('getName')->willReturn('textarea');
        
        // Mock field data
        $field1 = $this->createMock(Field::class);
        $field1->method('getId')->willReturn(1);
        $field1->method('getName')->willReturn('title');
        $field1->method('getType')->willReturn($fieldType1);
        $field1->method('isDisplay')->willReturn(true);

        $field2 = $this->createMock(Field::class);
        $field2->method('getId')->willReturn(2);
        $field2->method('getName')->willReturn('content');
        $field2->method('getType')->willReturn($fieldType2);
        $field2->method('isDisplay')->willReturn(true);

        // Mock style fields
        $styleField1 = $this->createMock(StylesField::class);
        $styleField1->method('getField')->willReturn($field1);
        $styleField1->method('getDefaultValue')->willReturn('Default Title');
        $styleField1->method('getHelp')->willReturn('Enter title');
        $styleField1->method('isDisabled')->willReturn(false);
        $styleField1->method('getHidden')->willReturn(0);

        $styleField2 = $this->createMock(StylesField::class);
        $styleField2->method('getField')->willReturn($field2);
        $styleField2->method('getDefaultValue')->willReturn('Default Content');
        $styleField2->method('getHelp')->willReturn('Enter content');
        $styleField2->method('isDisabled')->willReturn(false);
        $styleField2->method('getHidden')->willReturn(0);

        $styleFields = [$styleField1, $styleField2];
        
        $style = $this->createMock(Style::class);
        $style->method('getId')->willReturn($styleId);
        $style->method('getStylesFields')->willReturn($styleFields);
        
        $section->method('getStyle')->willReturn($style);
        $section->method('getName')->willReturn('Test Section');

        // Mock field types
        $fieldType1 = $this->createMock(FieldType::class);
        $fieldType1->method('getId')->willReturn(10);
        $fieldType1->method('getName')->willReturn('text');
        
        $fieldType2 = $this->createMock(FieldType::class);
        $fieldType2->method('getId')->willReturn(11);
        $fieldType2->method('getName')->willReturn('textarea');
        
        // Mock field data
        $field1 = $this->createMock(Field::class);
        $field1->method('getId')->willReturn(1);
        $field1->method('getName')->willReturn('title');
        $field1->method('getType')->willReturn($fieldType1);
        $field1->method('isDisplay')->willReturn(true);

        $field2 = $this->createMock(Field::class);
        $field2->method('getId')->willReturn(2);
        $field2->method('getName')->willReturn('content');
        $field2->method('getType')->willReturn($fieldType2);
        $field2->method('isDisplay')->willReturn(true);

        // Mock style fields
        $styleField1 = $this->createMock(StylesField::class);
        $styleField1->method('getField')->willReturn($field1);
        $styleField1->method('getDefaultValue')->willReturn('Default Title');
        $styleField1->method('getHelp')->willReturn('Enter title');
        $styleField1->method('isDisabled')->willReturn(false);
        $styleField1->method('getHidden')->willReturn(0);

        $styleField2 = $this->createMock(StylesField::class);
        $styleField2->method('getField')->willReturn($field2);
        $styleField2->method('getDefaultValue')->willReturn('Default Content');
        $styleField2->method('getHelp')->willReturn('Enter content');
        $styleField2->method('isDisabled')->willReturn(false);
        $styleField2->method('getHidden')->willReturn(0);

        $styleFields = [$styleField1, $styleField2];

        // Mock translations
        $language1 = $this->createMock(Language::class);
        $language1->method('getId')->willReturn(1);
        $language1->method('getLocale')->willReturn('en');

        $language2 = $this->createMock(Language::class);
        $language2->method('getId')->willReturn(2);
        $language2->method('getLocale')->willReturn('fr');

        $gender1 = $this->createMock(Gender::class);
        $gender1->method('getId')->willReturn(1);

        // Mock translation query builder
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $expr = $this->createMock(Expr::class);
        
        $queryBuilder->method('expr')->willReturn($expr);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('andWhere')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        
        // Mock translation results
        $translation1 = $this->createMock(SectionsFieldsTranslation::class);
        $translation1->method('getSection')->willReturn($section);
        $translation1->method('getField')->willReturn($field1);
        $translation1->method('getLanguage')->willReturn($language1);
        $translation1->method('getGender')->willReturn($gender1);
        $translation1->method('getContent')->willReturn('English Title');
        $translation1->method('getMeta')->willReturn(null);

        $translation2 = $this->createMock(SectionsFieldsTranslation::class);
        $translation2->method('getSection')->willReturn($section);
        $translation2->method('getField')->willReturn($field2);
        $translation2->method('getLanguage')->willReturn($language1);
        $translation2->method('getGender')->willReturn($gender1);
        $translation2->method('getContent')->willReturn('English Content');
        $translation2->method('getMeta')->willReturn('{"key":"value"}');

        $translations = [$translation1, $translation2];

        // Configure mocks
        $this->sectionRepository
            ->expects($this->once())
            ->method('find')
            ->with($sectionId)
            ->willReturn($section);

        $this->aclService
            ->expects($this->once())
            ->method('hasAccess')
            ->with($sectionId, 'select')
            ->willReturn(true);

        $this->entityManager
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder
            ->expects($this->once())
            ->method('getQuery')
            ->willReturn((object)['getResult' => function() use ($translations) { return $translations; }]);

        // Call the method
        $result = $this->adminSectionService->getSection($sectionId);

        // Assert the result structure
        $this->assertIsArray($result);
        $this->assertArrayHasKey('section', $result);
        $this->assertArrayHasKey('fields', $result);
        $this->assertArrayHasKey('languages', $result);

        // Assert section data
        $this->assertEquals($sectionId, $result['section']['id']);
        $this->assertEquals('Test Section', $result['section']['name']);
        $this->assertEquals($styleId, $result['section']['style']['id']);

        // Assert fields data
        $this->assertCount(2, $result['fields']);
        
        // First field assertions
        $this->assertEquals(1, $result['fields'][0]['id']);
        $this->assertEquals('title', $result['fields'][0]['name']);
        $this->assertEquals('text', $result['fields'][0]['type']);
        $this->assertEquals('Default Title', $result['fields'][0]['default_value']);
        $this->assertEquals('Enter title', $result['fields'][0]['help']);
        $this->assertEquals(false, $result['fields'][0]['disabled']);
        $this->assertEquals(false, $result['fields'][0]['hidden']);
        $this->assertEquals('block', $result['fields'][0]['display']);
        
        // Assert translations
        $this->assertCount(1, $result['fields'][0]['translations']);
        $this->assertEquals(1, $result['fields'][0]['translations'][0]['language_id']);
        $this->assertEquals('en', $result['fields'][0]['translations'][0]['language_code']);
        $this->assertEquals(1, $result['fields'][0]['translations'][0]['gender_id']);
        $this->assertEquals('English Title', $result['fields'][0]['translations'][0]['content']);
        
        // Assert languages
        $this->assertCount(1, $result['languages']);
        $this->assertEquals(1, $result['languages'][0]['id']);
        $this->assertEquals('en', $result['languages'][0]['locale']);
    }
}
