<?php

namespace Inclus16\LaravelDictionary\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Inclus16\LaravelDictionary\Handlers\DictionaryHandlerInterface;

class PublishDictionariesCommand extends Command
{
    public $name = 'Dictionaries publishing';

    public $signature = 'dictionaries:publish';

    public $description = 'Creates one json file with data from getData method from every dictionary in config.dictionary';

    public function handle(): void
    {
        $handlerClasses = Config::array('dictionary.handlers');
        $publishDisk = Config::string('dictionary.publishDisk');
        $store = Storage::disk($publishDisk);
        foreach ($handlerClasses as $handlerClass) {
            /** @var DictionaryHandlerInterface $handler * */
            $handler = App::make($handlerClass);
            if ($handler->toPublish()) {
                $data = $handler->getResponseEntities();
                $store->put("{$handler::getSlug()}.json", $data->toJson());
            }
        }
    }
}