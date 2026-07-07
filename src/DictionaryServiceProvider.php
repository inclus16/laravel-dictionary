<?php

namespace Inclus16\LaravelDictionary;

use Illuminate\Support\ServiceProvider;
use Inclus16\LaravelDictionary\Commands\PublishDictionariesCommand;
use Inclus16\LaravelDictionary\Facade\Dictionary;
use Inclus16\LaravelDictionary\Handlers\HandlerFactory;

final class DictionaryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(HandlerFactory::class);
        $this->app->bind('dictionaryHandlerFactory',HandlerFactory::class);
        $this->app->bind('dictionary',Dictionary::class);
    }
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                PublishDictionariesCommand::class
            ]);
        }
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'dictionary');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->publishes([
            __DIR__ . '/../config/dictionary.php' =>  $this->app->configPath('dictionary.php'),
            __DIR__ . '/../lang' => $this->app->langPath('vendor/dictionary')
        ]);
    }
}