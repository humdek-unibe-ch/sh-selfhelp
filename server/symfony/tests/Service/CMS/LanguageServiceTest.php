<?php

namespace App\Tests\Service\CMS;

use App\Entity\Language;
use App\Repository\LanguageRepository;
use App\Service\CMS\LanguageService;
use App\Util\EntityUtil;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LanguageServiceTest extends TestCase
{
    private LanguageRepository|MockObject $languageRepository;
    private EntityManagerInterface|MockObject $entityManager;
    private LanguageService $languageService;

    protected function setUp(): void
    {
        $this->languageRepository = $this->createMock(LanguageRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->languageService = new LanguageService($this->languageRepository, $this->entityManager);
    }

    public function testGetAllLanguages(): void
    {
        // Create mock languages
        $language1 = $this->createLanguage(1, 'en', 'English');
        $language2 = $this->createLanguage(2, 'de', 'German');
        
        // Set up repository mock
        $this->languageRepository->expects($this->once())
            ->method('findAllLanguages')
            ->willReturn([$language1, $language2]);
        
        // Call the service method
        $result = $this->languageService->getAllLanguages();
        
        // Assert the result
        $this->assertCount(2, $result);
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals('en', $result[0]['locale']);
        $this->assertEquals('English', $result[0]['language']);
        $this->assertEquals(2, $result[1]['id']);
        $this->assertEquals('de', $result[1]['locale']);
        $this->assertEquals('German', $result[1]['language']);
    }

    public function testGetAllNonDefaultLanguages(): void
    {
        // Create mock languages
        $language2 = $this->createLanguage(2, 'de', 'German');
        
        // Set up repository mock
        $this->languageRepository->expects($this->once())
            ->method('findAllExceptInternal')
            ->willReturn([$language2]);
        
        // Call the service method
        $result = $this->languageService->getAllNonInternalLanguages();
        
        // Assert the result
        $this->assertCount(1, $result);
        $this->assertEquals(2, $result[0]['id']);
        $this->assertEquals('de', $result[0]['locale']);
        $this->assertEquals('German', $result[0]['language']);
    }

    public function testGetLanguageById(): void
    {
        // Create mock language
        $language = $this->createLanguage(1, 'en', 'English');
        
        // Set up repository mock
        $this->languageRepository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($language);
        
        // Call the service method
        $result = $this->languageService->getLanguageById(1);
        
        // Assert the result
        $this->assertEquals(1, $result['id']);
        $this->assertEquals('en', $result['locale']);
        $this->assertEquals('English', $result['language']);
    }

    public function testGetLanguageByIdNotFound(): void
    {
        // Set up repository mock
        $this->languageRepository->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);
        
        // Expect exception
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Language not found');
        
        // Call the service method
        $this->languageService->getLanguageById(999);
    }

    public function testCreateLanguage(): void
    {
        // Create language data
        $data = [
            'locale' => 'fr',
            'language' => 'French',
            'csv_separator' => ';'
        ];
        
        // Set up entity manager mock to capture the persisted entity
        $capturedLanguage = null;
        $this->entityManager
            ->method('persist')
            ->willReturnCallback(function ($language) use (&$capturedLanguage) {
                $capturedLanguage = $language;
            });
        
        // Call the service method
        $result = $this->languageService->createLanguage($data);
        
        // Assert that persist was called with a Language entity with the correct properties
        $this->assertNotNull($capturedLanguage, 'Entity manager persist() was not called');
        $this->assertInstanceOf(Language::class, $capturedLanguage);
        $this->assertEquals('fr', $capturedLanguage->getLocale());
        $this->assertEquals('French', $capturedLanguage->getLanguage());
        $this->assertEquals(';', $capturedLanguage->getCsvSeparator());
        
        // Assert the result
        $this->assertEquals('fr', $result['locale']);
        $this->assertEquals('French', $result['language']);
        $this->assertEquals(';', $result['csv_separator']);
    }

    public function testCreateLanguageWithInvalidData(): void
    {
        // Create invalid language data (missing locale)
        $data = [
            'language' => 'French'
        ];
        
        // Expect exception
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Locale is required');
        
        // Call the service method
        $this->languageService->createLanguage($data);
    }

    public function testUpdateLanguage(): void
    {
        // Create mock language
        $language = $this->createLanguage(2, 'fr', 'French');
        
        // Create update data
        $data = [
            'locale' => 'fr-FR',
            'language' => 'French (France)'
        ];
        
        // Set up repository mock
        $this->languageRepository
            ->method('find')
            ->with(2)
            ->willReturn($language);
        
        // Call the service method
        $result = $this->languageService->updateLanguage(2, $data);
        
        // Assert the result
        $this->assertEquals(2, $result['id']);
        $this->assertEquals('fr-FR', $result['locale']);
        $this->assertEquals('French (France)', $result['language']);
    }

    public function testUpdateLanguageNotFound(): void
    {
        // Set up repository mock
        $this->languageRepository->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);
        
        // Expect exception
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Language not found');
        
        // Call the service method
        $this->languageService->updateLanguage(999, ['locale' => 'fr', 'language' => 'French']);
    }

    public function testUpdateDefaultLanguage(): void
    {
        // Create mock language
        $language = $this->createLanguage(1, 'en', 'English');
        
        // Set up repository mock
        $this->languageRepository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($language);
        
        // Expect exception
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Cannot update the default language');
        
        // Call the service method
        $this->languageService->updateLanguage(1, ['locale' => 'en-US', 'language' => 'English (US)']);
    }

    public function testDeleteLanguage(): void
    {
        // Create mock language
        $language = $this->createLanguage(2, 'fr', 'French');
        
        // Set up repository mock
        $this->languageRepository
            ->method('find')
            ->with(2)
            ->willReturn($language);
        
        // Set up entity manager mock to verify remove was called with the language
        $removedLanguage = null;
        $this->entityManager
            ->method('remove')
            ->willReturnCallback(function ($entity) use (&$removedLanguage) {
                $removedLanguage = $entity;
            });
        
        // Call the service method
        $result = $this->languageService->deleteLanguage(2);
        
        // Assert that remove was called with the correct language
        $this->assertSame($language, $removedLanguage);
        
        // Assert the result
        $this->assertTrue($result);
    }

    public function testDeleteLanguageNotFound(): void
    {
        // Set up repository mock
        $this->languageRepository->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);
        
        // Expect exception
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Language not found');
        
        // Call the service method
        $this->languageService->deleteLanguage(999);
    }

    public function testDeleteDefaultLanguage(): void
    {
        // Create mock language
        $language = $this->createLanguage(1, 'en', 'English');
        
        // Set up repository mock
        $this->languageRepository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($language);
        
        // Expect exception
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Cannot delete the default language');
        
        // Call the service method
        $this->languageService->deleteLanguage(1);
    }

    /**
     * Helper method to create a Language entity
     */
    private function createLanguage(int $id, string $locale, string $language, string $csvSeparator = ','): Language
    {
        $languageEntity = new Language();
        
        // Use reflection to set the ID (since it's private and has no setter)
        $reflection = new \ReflectionClass(Language::class);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($languageEntity, $id);
        
        $languageEntity->setLocale($locale);
        $languageEntity->setLanguage($language);
        $languageEntity->setCsvSeparator($csvSeparator);
        
        return $languageEntity;
    }
}
