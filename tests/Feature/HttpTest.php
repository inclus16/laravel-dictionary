<?php

namespace Inclus16\LaravelDictionary\Tests\Feature;

use Faker\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\View\ViewServiceProvider;
use Inclus16\LaravelDictionary\DictionaryServiceProvider;
use Inclus16\LaravelDictionary\Handlers\AbstractDictionaryHandler;
use Inclus16\LaravelDictionary\Handlers\HandlerFactory;
use Inclus16\LaravelDictionary\Http\DictionaryController;
use Inclus16\LaravelDictionary\Http\HttpEntityProvider;
use Inclus16\LaravelDictionary\Http\SimpleEntityResponse;
use Inclus16\LaravelDictionary\Tests\Mocks\CacheMocks;
use Inclus16\LaravelDictionary\Tests\Mocks\ConfigMocks;
use Inclus16\LaravelDictionary\Tests\TestEntities\DictionaryHandlers\Simple;
use Inclus16\LaravelDictionary\Tests\TestEntities\Entity;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CoversClass(DictionaryController::class)]
#[CoversClass(DictionaryServiceProvider::class)]
#[CoversClass(AbstractDictionaryHandler::class)]
#[CoversClass(HandlerFactory::class)]
#[CoversClass(HttpEntityProvider::class)]
#[CoversClass(SimpleEntityResponse::class)]
class HttpTest extends TestCase
{
    use CacheMocks;
    use ConfigMocks;

    private const ENTITIES_NAMES = ['test', 'test2', 'test3'];
    private Collection $entities;

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.debug', false);
        $app['config']->set('view.paths', [
            realpath(__DIR__ . '/../resources/views'),
        ]);
        $app['config']->set('view.compiled', sys_get_temp_dir() . '/tests-compiled');
    }

    protected function setUp(): void
    {
        $this->entities = collect();
        foreach (self::ENTITIES_NAMES as $i => $entityName) {
            $this->entities->push(new Entity($i + 1, $entityName));
        }
        parent::setUp();
    }


    protected function getPackageProviders($app): array
    {
        return [
            ViewServiceProvider::class,
            DictionaryServiceProvider::class
        ];
    }

    public function testGetSuccess(): void
    {
        $this->mockCacheSimpleEntity();
        $this->mockConfig();
        Auth::spy()->shouldReceive('check')->once()->andReturn(true);
        $response = $this->getJson('/api/dictionary/test');
        $response->assertStatus(200);
        $response->assertJson($this->entities->map(fn(Entity $entity) => [
            'id' => $entity->id,
            'name' => $entity->name,
        ])->toArray());
    }


    public function testGetNotFound(): void
    {
        $this->withoutExceptionHandling();
        $faker = Factory::create();
        $this->assertThrows(function () use ($faker) {
            $this->getJson('/api/dictionary/' . $faker->title());
        }, NotFoundHttpException::class);
    }

    public function testGetUnauthorized(): void
    {
        $config = Config::spy()->makePartial();
        $config->shouldReceive('get')
            ->withArgs(['dictionary.handlers'])
            ->andReturn([Simple::getSlug() => Simple::class]);
        $this->withoutExceptionHandling();
        Auth::spy()->shouldReceive('check')->once()->andReturn(false);
        $this->assertThrows(function () {
            $this->getJson('/api/dictionary/test');
        }, UnauthorizedException::class);

    }
}