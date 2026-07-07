<?php

namespace Inclus16\LaravelDictionary\Tests\Unit;

use Illuminate\Support\Collection;
use Inclus16\LaravelDictionary\Commands\PublishDictionariesCommand;
use Inclus16\LaravelDictionary\Handlers\AbstractDictionaryHandler;
use Inclus16\LaravelDictionary\Http\SimpleEntityResponse;
use Orchestra\Testbench\TestCase;
use Inclus16\LaravelDictionary\DictionaryServiceProvider;
use Inclus16\LaravelDictionary\Facade\Dictionary;
use Inclus16\LaravelDictionary\Handlers\HandlerFactory;
use Inclus16\LaravelDictionary\Tests\Mocks\CacheMocks;
use Inclus16\LaravelDictionary\Tests\Mocks\ConfigMocks;
use Inclus16\LaravelDictionary\Tests\TestEntities\Entity;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HandlerFactory::class)]
#[CoversClass(Dictionary::class)]
#[CoversClass(DictionaryServiceProvider::class)]
class FacadeTest extends TestCase
{

    use CacheMocks;
    use ConfigMocks;

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

    public function testGet()
    {
        $instance1 = Dictionary::getFacadeRoot();
        $instance2 = app('dictionary')->getFacadeRoot();
        $this->assertInstanceOf(HandlerFactory::class, $instance1);
        $this->assertSame($instance1, $instance2);
    }
}