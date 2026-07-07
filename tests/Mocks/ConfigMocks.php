<?php

namespace Inclus16\LaravelDictionary\Tests\Mocks;

use Illuminate\Support\Facades\Config;
use Inclus16\LaravelDictionary\Tests\TestEntities\DictionaryHandlers\Simple;
use Orchestra\Testbench\Exceptions\Handler;

trait ConfigMocks
{
    public function mockConfig(): void
    {
        $ttl = 5;
        $config = Config::spy()->makePartial();
        $config->shouldReceive('integer')
            ->withArgs(['dictionary.default_cache_ttl'])
            ->andReturn($ttl)
            ->once();
        $config->shouldReceive('get')
            ->withArgs(['dictionary.handlers'])
            ->andReturn([Simple::getSlug() => Simple::class]);
        $config->shouldReceive('string')
            ->andReturn('public');
    }
}