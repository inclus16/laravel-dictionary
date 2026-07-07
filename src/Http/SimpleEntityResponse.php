<?php

namespace Inclus16\LaravelDictionary\Http;

readonly final class SimpleEntityResponse implements \JsonSerializable
{
    public function __construct(private mixed $entity)
    {
    }


    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->entity->id,
            'name' => $this->entity->name
        ];
    }
}