<?php

namespace Inclus16\LaravelDictionary\Handlers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

readonly final class HandlerFactory
{
    private Collection $handlersMapping;

    public function __construct()
    {
        $this->handlersMapping = new Collection(Config::get('dictionary.handlers'));
    }


    public function getHandler(string $slug): DictionaryHandlerInterface
    {
        if (!$this->handlersMapping->has($slug)) {
            throw new \OutOfRangeException("Dictionary with slug $slug is not registered in config");
        }
        return App::make($this->handlersMapping->get($slug));
    }
}