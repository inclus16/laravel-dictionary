<?php

use Illuminate\Support\Facades\Route;
use Inclus16\LaravelDictionary\Http\DictionaryController;

Route::prefix('api/dictionary')
    ->middleware('api')
    ->group(function () {
        Route::get('/{slug}', [DictionaryController::class, 'get'])->name('dictionary.get');
    });