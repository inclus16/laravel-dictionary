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

    protected function getResponseEntityClass(): string
    {
        return SimpleEntityResponse::class;
    }

    protected function getCacheStore(): Repository
    {
        return Cache::store(Config::string('dictionary.default_cache_store'));
    }

    protected function getCacheTtl(): int
    {
        return Config::integer('dictionary.default_cache_ttl');
    }

    public function authorize(): bool
    {
        return Auth::check();
    }

    public function toPublish(): bool
    {
        return true;
    }

    protected abstract function fetchUncachedEntities(): Collection;

    public function getEntities(): Collection
    {
        return $this->getCacheStore()->remember($this->getSlug(), $this->getCacheTtl(), fn() => $this->fetchUncachedEntities());
    }

    public function clearCache(): void
    {
        $this->getCacheStore()->forget($this->getSlug());
    }

    public function getResponseEntities(): Collection
    {
        $responseEntityClass = $this->getResponseEntityClass();
        return $this->getEntities()->map(fn(mixed $entity) => new $responseEntityClass($entity));
    }
}