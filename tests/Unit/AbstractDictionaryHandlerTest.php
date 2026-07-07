<?php

namespace Inclus16\LaravelDictionary\Tests\Unit;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Inclus16\LaravelDictionary\Handlers\AbstractDictionaryHandler;
use Inclus16\LaravelDictionary\Http\SimpleEntityResponse;
use Inclus16\LaravelDictionary\Tests\Mocks\CacheMocks;
use Inclus16\LaravelDictionary\Tests\Mocks\ConfigMocks;
use Inclus16\LaravelDictionary\Tests\TestEntities\DictionaryHandlers\Simple;
use Inclus16\LaravelDictionary\Tests\TestEntities\DictionaryHandlers\SimpleTtlOverrided;
use Inclus16\LaravelDictionary\Tests\TestEntities\DictionaryHandlers\WithIdResponseEntity;
use Inclus16\LaravelDictionary\Tests\TestEntities\Entity;
use Inclus16\LaravelDictionary\Tests\TestEntities\ResponseEntities\IdResponseEntity;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;


#[CoversClass(AbstractDictionaryHandler::class)]
#[CoversClass(SimpleEntityResponse::class)]
class AbstractDictionaryHandlerTest extends TestCase
{
    use CacheMocks;
    use ConfigMocks;

    private Collection $entities;

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


    public function testGetData()
    {
        $this->mockCacheSimpleEntity();
        $this->mockConfig();
        $handler = new Simple();
        $entities = $handler->getEntities();
        self::assertEquals($this->entities, $entities);
    }

    public function testGetResponse()
    {
        $this->mockCacheSimpleEntity();
        $this->mockConfig();
        $handler = new Simple();
        $responseEntities = $handler->getResponseEntities();
        self::assertEquals($this->entities->map(fn(Entity $e) => new SimpleEntityResponse($e)), $responseEntities);
    }

    public function testNotDefaultResponseEntity()
    {
        $this->mockCacheSimpleEntity();
        $this->mockConfig();
        $handler = new WithIdResponseEntity();
        $responseEntities = $handler->getResponseEntities();
        self::assertEquals($this->entities->map(fn(Entity $e) => new IdResponseEntity($e)), $responseEntities);
    }

    public function testOverrideTtl()
    {
        $ttl = 255;
        $repositoryMock = \Mockery::mock(Repository::class);
        $repositoryMock->shouldReceive('remember')
            ->with(SimpleTtlOverrided::getSlug(), $ttl, \Closure::class)
            ->andReturn($this->entities);
        $cacheRepository = $repositoryMock;
        $config = Config::spy();
        $config->shouldNotReceive('integer')
            ->withArgs(['dictionary.default_cache_ttl']);
        $config->shouldReceive('string')
            ->withArgs(['dictionary.default_cache_store'])
            ->andReturn('test')
            ->once();
        $cache = Cache::spy();
        $cache->shouldReceive('store')
            ->andReturn($cacheRepository)
            ->once();
        $handler = new SimpleTtlOverrided();
        $this->assertEquals($ttl, $handler->getCacheTtl());
        $entities = $handler->getEntities();
        self::assertEquals($this->entities, $entities);
    }

    public function testClearCache()
    {
        $config = Config::spy();
        $config->shouldReceive('string')
            ->withArgs(['dictionary.default_cache_store'])
            ->andReturn(Simple::getSlug())
            ->once();

        $repositoryMock = \Mockery::mock(Repository::class);
        $cacheRepository = $repositoryMock;
        $cacheRepository->shouldReceive('forget')
            ->with(Simple::getSlug())
            ->once();
        $cache = Cache::spy();
        $cache->shouldReceive('store')
            ->once()
            ->withArgs([Simple::getSlug()])
            ->andReturn($cacheRepository);
        $handler = new Simple();
        $handler->clearCache();
    }
}