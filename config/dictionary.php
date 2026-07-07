<?php

return [
    'default_cache_store' => env('DICTIONARY_DEFAULT_CACHE_STORE', 'array'),
    'default_cache_ttl' => env('DICTIONARY_DEFAULT_CACHE_TTL', 3600),
    'handlers' => [

    ],
    'publishDisk' => 'public'
];