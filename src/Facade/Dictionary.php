<?php

namespace Inclus16\LaravelDictionary\Facade;

use Illuminate\Support\Facades\Facade;
use Inclus16\LaravelDictionary\Handlers\DictionaryHandlerInterface;
use Inclus16\LaravelDictionary\Handlers\HandlerFactory;

/**
 * @method static DictionaryHandlerInterface getHandler(string $slug)
 * @see HandlerFactory
 */
class Dictionary extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'dictionaryHandlerFactory';
    }
}