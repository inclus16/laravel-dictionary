<?php

namespace Inclus16\LaravelDictionary\Tests\Mocks;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Inclus16\LaravelDictionary\Tests\TestEntities\DictionaryHandlers\Simple;

/**
 * @property Collection $entities
 */
trait CacheMocks
{
    protected function mockCacheSimpleEntity(): void
    {
        $repositoryMock = \Mockery::mock(Repository::class);
        $repositoryMock->shouldReceive('remember')
            ->with(Simple::getSlug(), 5, \Closure::class)->andReturn($this->entities);
        $cacheRepository = $repositoryMock;
        $cache = Cache::spy();
        $cache->shouldReceive('store')
            ->andReturn($cacheRepository)
            ->once();
    }
}