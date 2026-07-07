<?php

namespace Inclus16\LaravelDictionary\Handlers;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Inclus16\LaravelDictionary\Http\SimpleEntityResponse;

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