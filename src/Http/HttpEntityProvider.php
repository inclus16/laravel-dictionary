<?php

namespace Inclus16\LaravelDictionary\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\UnauthorizedException;
use Inclus16\LaravelDictionary\Handlers\DictionaryHandlerInterface;
use Inclus16\LaravelDictionary\Handlers\HandlerFactory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly final class HttpEntityProvider
{

    public function __construct(private HandlerFactory $handlerFactory)
    {
    }

    public function getHandler(string $slug): DictionaryHandlerInterface
    {
        try {
            $handler = $this->handlerFactory->getHandler($slug);
        } catch (\OutOfRangeException) {
            throw new NotFoundHttpException();
        }
        if (!$handler->authorize()) {
            throw new UnauthorizedException();
        }
        return $handler;
    }
}