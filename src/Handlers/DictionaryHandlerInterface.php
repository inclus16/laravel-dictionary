<?php

namespace Inclus16\LaravelDictionary\Handlers;

use Illuminate\Support\Collection;

interface DictionaryHandlerInterface
{
    public static function getSlug(): string;

    public function getEntities(): Collection;

    public function getResponseEntities(): Collection;

    public function authorize(): bool;

    public function getResponseCacheSeconds(): ?int;

    public function toPublish(): bool;

    public function clearCache(): void;
}