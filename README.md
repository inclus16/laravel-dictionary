# Laravel dictionary

Laravel dictionary is a package, that manages access, validation and cache for your collection-based data.

A dictionary is a static (or nearly static) set of data that needs to be reread very often.

## Install

``` bash
//TODO
```
The package will automatically register a service provider
Publish the package's configuration and translations by running:
``` bash
php artisan vendor:publish --provider="Inclus16\LaravelDictionary\DictionaryServiceProvider"
```

## Configuration
In .env file there constants that can be added:

DICTIONARY_DEFAULT_CACHE_STORE - see Laravel cache (default: array)

DICTIONARY_DEFAULT_CACHE_TTL - see Laravel cache (default: 3600)

## Usage
The main class that you may work with is some classes that may be called DictionaryHandler.

```php
class PageType extends \Inclus16\LaravelDictionary\Handlers\AbstractDictionaryHandler
{
    /**
    * Use this slug for access this handler in validation, http, files, or in your code.
    * @return string
     */
    public static function getSlug() : string{
        return 'page_types';
    }
}
```
this class must be registered in config dictionary.php:
```php
return [
    'default_cache_store' => env('DICTIONARY_DEFAULT_CACHE_STORE', 'array'),
    'default_cache_ttl' => env('DICTIONARY_DEFAULT_CACHE_TTL', 3600),
    'handlers' => [
        PageType::getSlug() => PageType::class
    ],
    'publishDisk' => 'public'
];
```

Full methods of AbstractDictionaryHandler that can be extended:
```php
abstract class AbstractDictionaryHandler implements DictionaryHandlerInterface
{

    /**
     * What response class must be used
     * @return string
     */
    protected function getResponseEntityClass(): string
    {
        return SimpleEntityResponse::class;
    }

    /**
     * What cache store must be used (see Laravel cache)
     * @return bool
     */
    protected function getCacheStore(): Repository
    {
        return Cache::store(Config::string('dictionary.default_cache_store'));
    }

    /**
     * How many times cache should live (see Laravel cache)
     * @return bool
     */
    protected function getCacheTtl(): int
    {
        return Config::integer('dictionary.default_cache_ttl');
    }

    /**
     * This method called only when http access happens.
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Whenever this dictionary must be stored on disk as json file. If so nginx can handle it directly. For optimization purpose for most times
     * @return bool
     */
    public function toPublish(): bool
    {
        return true;
    }

    /**
     * Main method you must implement. How DictionaryHandler will fetch fresh, uncached data (from database as example)
     * @return Collection
     */
    protected abstract function fetchUncachedEntities(): Collection;

    /**
     * Usually used in code
     * @return Collection
     */
    public function getEntities(): Collection
    {
        return $this->getCacheStore()->remember($this->getSlug(), $this->getCacheTtl(), fn() => $this->fetchUncachedEntities());
    }

    /**
     * Forget cached data from cache. Useful when your dictionary data was updated (database insert/update)
     * @return void
     */
    public function clearCache(): void
    {
        $this->getCacheStore()->forget($this->getSlug());
    }

    /**
     * Used only in http access. If null - no http response headers will be added. If not null - will add header Cache-Control: public, max-age={x}
     * @return int|null
     */
    public function getResponseCacheSeconds(): ?int
    {
        return 604800;//one week
    }

    /**
     *  Transforms cached collection of entities to collection of response entities
     * @return Collection
     */
    public function getResponseEntities(): Collection
    {
        $responseEntityClass = $this->getResponseEntityClass();
        return $this->getEntities()->map(fn(mixed $entity) => new $responseEntityClass($entity));
    }
}
```
### Validation
For validation, you may use one of two rules:
```php
'fieldFromRequest' => [new InDictionary(string $slug, string $field)]
```
This rule will search in dictionary {slug}, iterates over cached entities and compare value from request with {field} value from entity.
If entity with equality will found - validation will be passed 

```php
'fieldFromRequest' => [new  NotInDictionary(string $slug, string $field)]
```
This rule will search in dictionary {slug}, iterates over cached entities and compare value from request with {field} value from entity.
If entity with equality does NOT found - validation will be passed

### Access in code
#### Facade
```php
\Inclus16\LaravelDictionary\Facade\Dictionary::getHandler(string $slug)
```
Just one method. This will return a dictionary handler with this {slug}, or throw OutOfRangeException
#### DI
Inject \Inclus16\LaravelDictionary\Handler\HandlerFactory class
### Static files
For optimization purposes you may call
```bash
php artisan dictionaries:publish
```
this command with create json files that contains data of dictionary.
for each dictionary there will be created a file, named {dictionarySlug}.json in disk from config: dictionary.publishDisk.
Then you can handle this files directly via nginx as static files
### Http access
```http request
GET /api/dictionary/{slug}
```