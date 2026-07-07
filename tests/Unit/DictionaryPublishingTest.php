<?php

namespace Inclus16\LaravelDictionary\Tests\Unit;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Inclus16\LaravelDictionary\Commands\PublishDictionariesCommand;
use Inclus16\LaravelDictionary\DictionaryServiceProvider;
use Inclus16\LaravelDictionary\Handlers\AbstractDictionaryHandler;
use Inclus16\LaravelDictionary\Http\SimpleEntityResponse;
use Inclus16\LaravelDictionary\Tests\TestEntities\DictionaryHandlers\Simple;
use Inclus16\LaravelDictionary\Tests\TestEntities\DictionaryHandlers\SimpleNotPublishing;
use Inclus16\LaravelDictionary\Tests\TestEntities\Entity;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SimpleEntityResponse::class)]
#[CoversClass(AbstractDictionaryHandler::class)]
#[CoversClass(PublishDictionariesCommand::class)]
#[CoversClass(DictionaryServiceProvider::class)]
class DictionaryPublishingTest extends TestCase
{
    private Collection $entities;

    protected function getPackageProviders($app): array
    {
        return [
            DictionaryServiceProvider::class
        ];
    }
    protected function setUp(): void
    {
        $this->entities = new Collection([
            new Entity(1, 'test'),
            new Entity(2, 'test2'),
            new Entity(3, 'test3')
        ]);
        parent::setUp();
    }

    protected function tearDown(): void
    {
        Config::clearResolvedInstances();
        Cache::clearResolvedInstances();
        parent::tearDown();
    }

    public function testStore(): void
    {
        $ttl = 5;
        $repositoryMock = \Mockery::mock(Repository::class);
        $repositoryMock->shouldReceive('remember')
            ->with(Simple::getSlug(), 5, \Closure::class)->andReturn($this->entities);
        $cacheRepository = $repositoryMock;
        $cache = Cache::spy();
        $cache->shouldReceive('store')
            ->andReturn($cacheRepository)
            ->once();
        $config = Config::spy();
        $config->shouldReceive('integer')
            ->withArgs(['dictionary.default_cache_ttl'])
            ->andReturn($ttl)
            ->once();
        $config->shouldReceive('string')
            ->andReturn('public');
        $config->shouldReceive('array')
            ->once()
            ->andReturn([Simple::class]);
        $storage = Storage::spy();
        $storage->shouldReceive('disk')
            ->once()
            ->andReturn($storage);
        $storage->shouldReceive('put')
            ->once()->withArgs([Simple::getSlug() . '.json', $this->entities->toJson()]);
        $this->artisan('dictionaries:publish')->assertExitCode(0);
    }

    public function testDontStoreDictionaryNotPublishing(): void
    {
        $config = Config::spy();
        $config->shouldReceive('string')
            ->andReturn('public');
        $config->shouldReceive('array')
            ->andReturn([SimpleNotPublishing::class]);
        $storage = Storage::spy();
        $storage->shouldReceive('disk')
            ->once()
            ->andReturn($storage);
        $storage->shouldNotReceive('put');
        $this->artisan('dictionaries:publish')->assertExitCode(0);
    }
}