# CMS Translation System

## Current Implementation

- **Default language in CMS preferences**: Already implemented in the `CmsPreference` entity with the `defaultLanguage` relationship
- **Content storage**: Content entered in pages/sections is saved with the default language ID
- **Translation workflow**: A dedicated translation module allows users to translate all page content at once for all languages
- **Language fallback**: When loading content, the system uses the user's selected language, falling back to the default language if no translation exists

## Core Translation Principles

1. **Default language as source**: All initial content is saved in the default language
2. **Centralized translation interface**: Translators can work efficiently with all content in one place
3. **Automatic fallback mechanism**: Ensures content is always available even when translations are incomplete
4. **Database-driven translations**: Dynamic content is stored in translation tables with language associations

## Recommendations for Improvement

### 1. Symfony Translation Component Integration

Integrate Symfony's built-in translation component for static text, complementing database-driven translations for dynamic content:

- **Static vs. Dynamic Content**: Use Symfony translations for UI elements, error messages, and static text; use database translations for user-generated content
- **Translation files**: Store translations in YAML/XML/PHP files in `translations/` directory
- **Message extraction**: Automatically extract translatable strings from templates and controllers

#### Implementation Guide for Database-Driven Translations

To integrate database translations with Symfony's translation component:

1. **Create a Custom Translation Loader**

```php
// src/Translation/DatabaseLoader.php
namespace App\Translation;

use App\Repository\PagesFieldsTranslationRepository;
use App\Repository\SectionsFieldsTranslationRepository;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

class DatabaseLoader implements LoaderInterface
{
    private PagesFieldsTranslationRepository $pagesTranslationRepo;
    private SectionsFieldsTranslationRepository $sectionsTranslationRepo;
    
    public function __construct(
        PagesFieldsTranslationRepository $pagesTranslationRepo,
        SectionsFieldsTranslationRepository $sectionsTranslationRepo
    ) {
        $this->pagesTranslationRepo = $pagesTranslationRepo;
        $this->sectionsTranslationRepo = $sectionsTranslationRepo;
    }
    
    public function load($resource, string $locale, string $domain = 'messages'): MessageCatalogue
    {
        $catalogue = new MessageCatalogue($locale);
        
        // Load page translations
        $pageTranslations = $this->pagesTranslationRepo->findByLanguageLocale($locale);
        foreach ($pageTranslations as $translation) {
            $key = sprintf('page.%d.field.%d', 
                $translation->getIdPages(), 
                $translation->getIdFields()
            );
            $catalogue->set($key, $translation->getContent(), $domain);
        }
        
        // Load section translations
        $sectionTranslations = $this->sectionsTranslationRepo->findByLanguageLocale($locale);
        foreach ($sectionTranslations as $translation) {
            $key = sprintf('section.%d.field.%d', 
                $translation->getIdSections(), 
                $translation->getIdFields()
            );
            $catalogue->set($key, $translation->getContent(), $domain);
        }
        
        return $catalogue;
    }
}
```

2. **Add Repository Methods**

```php
// src/Repository/PagesFieldsTranslationRepository.php
public function findByLanguageLocale(string $locale): array
{
    return $this->createQueryBuilder('pt')
        ->join('pt.language', 'l')
        ->where('l.locale = :locale')
        ->setParameter('locale', $locale)
        ->getQuery()
        ->getResult();
}

// Similar method for SectionsFieldsTranslationRepository
```

3. **Register the Custom Loader**

```yaml
# config/services.yaml
services:
    App\Translation\DatabaseLoader:
        tags:
            - { name: translation.loader, alias: db }
```

4. **Configure Translation Resources**

```yaml
# config/packages/translation.yaml
framework:
    default_locale: '%locale%'
    translator:
        default_path: '%kernel.project_dir%/translations'
        fallbacks:
            - '%locale%'
        paths:
            - '%kernel.project_dir%/translations'
        loaders:
            - db
```

5. **Create a Translation Cache Warmer**

```php
// src/Translation/DatabaseTranslationCacheWarmer.php
namespace App\Translation;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\Translation\TranslatorBagInterface;

class DatabaseTranslationCacheWarmer implements CacheWarmerInterface
{
    private TranslatorBagInterface $translator;
    private array $locales;
    
    public function __construct(TranslatorBagInterface $translator, array $locales)
    {
        $this->translator = $translator;
        $this->locales = $locales;
    }
    
    public function isOptional(): bool
    {
        return true;
    }
    
    public function warmUp(string $cacheDir): array
    {
        foreach ($this->locales as $locale) {
            // This will trigger the loading of translations from the database
            // and cache them
            $this->translator->getCatalogue($locale);
        }
        
        return [];
    }
}
```

Register it in `config/services.yaml`:

```yaml
services:
    App\Translation\DatabaseTranslationCacheWarmer:
        arguments:
            $locales: ['en', 'de', 'fr'] # Add your supported locales
        tags:
            - { name: kernel.cache_warmer }
```

6. **Using Database Translations**

In controllers:
```php
// In a controller
$translatedContent = $translator->trans('page.123.field.456');
```

In Twig templates:
```twig
{# In a Twig template #}
{{ 'page.123.field.456'|trans }}
```

7. **Cache Invalidation**

When translations are updated in the database, clear the translation cache:

```php
// In your translation update service
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

class TranslationUpdateService
{
    private CacheClearerInterface $cacheClearer;
    private string $cacheDir;
    
    public function __construct(CacheClearerInterface $cacheClearer, string $cacheDir)
    {
        $this->cacheClearer = $cacheClearer;
        $this->cacheDir = $cacheDir;
    }
    
    public function updateTranslation(/* ... */)
    {
        // Update translation in database
        // ...
        
        // Clear translation cache
        $this->cacheClearer->clear($this->cacheDir);
    }
}
```

### 2. Translation Interface Improvements

- Group translations by content type (pages, sections)
- Show side-by-side translation editing with the default language as reference
- Add visual indicators for missing translations

### 3. Batch Translation Operations

- Add functionality to export/import translations (CSV/XLSX)
- Implement batch translation status updates

### 4. Translation Caching

- Implement a caching layer for translations to improve performance
- Use Symfony's cache component with tags for efficient invalidation