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
        $handler = $this->entityProvider->getHandler($slug);
        $response = new JsonResponse($handler->getResponseEntities());
        if (($seconds = $handler->getResponseCacheSeconds()) !== null) {
            $response->withHeaders([
                'Cache-Control' => 'public, max-age=' . $seconds
            ]);
        }
        return $response;
    }
}