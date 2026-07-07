<?php

namespace Inclus16\LaravelDictionary\Tests\Unit\Validation;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Inclus16\LaravelDictionary\DictionaryServiceProvider;
use Inclus16\LaravelDictionary\Handlers\AbstractDictionaryHandler;
use Inclus16\LaravelDictionary\Handlers\HandlerFactory;
use Inclus16\LaravelDictionary\Tests\Mocks\CacheMocks;
use Inclus16\LaravelDictionary\Tests\Mocks\ConfigMocks;
use Inclus16\LaravelDictionary\Tests\TestEntities\Entity;
use Inclus16\LaravelDictionary\Validation\Rules\NotInDictionary;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(NotInDictionary::class)]
#[CoversClass(DictionaryServiceProvider::class)]
#[CoversClass(AbstractDictionaryHandler::class)]
#[CoversClass(HandlerFactory::class)]
class NotInDictionaryValidationRuleTest extends TestCase
{
    use CacheMocks;
    use ConfigMocks;

    private const ENTITIES_NAMES = ['test', 'test2', 'test3'];

    private Collection $entities;


    protected function setUp(): void
    {
        $this->entities = collect([]);
        foreach (self::ENTITIES_NAMES as $i => $entityName) {
            $this->entities->push(new Entity($i + 1, $entityName));
        }
        parent::setUp();
        App::setLocale('en');
        App::registerDeferredProvider(DictionaryServiceProvider::class);
    }

    protected function tearDown(): void
    {
        Config::clearResolvedInstances();
        Cache::clearResolvedInstances();
        parent::tearDown();
    }

    public function testValidationPassed()
    {
        $data = [
            'testField' => uniqid()
        ];

        $this->mockCacheSimpleEntity();
        $this->mockConfig();
        $result = Validator::make($data, [
            'testField' => new NotInDictionary('test', 'name')
        ]);
        self::assertEmpty($result->errors());
    }

    #[DataProvider('provideExistingValues')]
    public function testValidationFailed(string $entityName)
    {
        $data = [
            'testField' => $entityName
        ];
        $this->mockCacheSimpleEntity();
        $this->mockConfig();
        $result = Validator::make($data, [
            'testField' => new NotInDictionary('test', 'name')
        ]);
        self::assertEquals([
            'testField' => ["The {$data['testField']} already exists in dictionary test"]
        ], $result->errors()->messages());
    }

    public static function provideExistingValues(): array
    {
        return array_map(fn(string $name) => [$name], self::ENTITIES_NAMES);
    }
}