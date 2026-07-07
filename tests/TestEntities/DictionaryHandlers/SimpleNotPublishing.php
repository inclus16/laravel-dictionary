<?php

namespace Inclus16\LaravelDictionary\Tests\TestEntities\DictionaryHandlers;

use Illuminate\Support\Collection;
use Inclus16\LaravelDictionary\Handlers\AbstractDictionaryHandler;
use Inclus16\LaravelDictionary\Tests\TestEntities\Entity;

class SimpleNotPublishing extends AbstractDictionaryHandler
{
    private Collection $entities;

    public function toPublish(): bool
    {
        return false;
    }

    public function __construct()
    {
        $this->entities = new Collection([
            new Entity(1, 'Vasya'),
            new Entity(2, 'John'),
            new Entity(3, 'Anna')
        ]);
    }

    public function getCacheTtl(): int
    {
        return 5;
    }

    public static function getSlug(): string
    {
        return 'test';
    }

    protected function fetchUncachedEntities(): Collection
    {
        return $this->entities;
    }

}