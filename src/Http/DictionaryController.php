<?php

namespace Inclus16\LaravelDictionary\Http;

use Illuminate\Http\JsonResponse;

readonly final class DictionaryController
{
    public function __construct(private HttpEntityProvider $entityProvider)
    {

    }

    public function get(string $slug): JsonResponse
    {
        return $this->entityProvider->getAsJsonResponse($slug);
    }
}