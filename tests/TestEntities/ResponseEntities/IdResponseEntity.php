<?php

namespace Inclus16\LaravelDictionary\Tests\TestEntities\ResponseEntities;

use Inclus16\LaravelDictionary\Tests\TestEntities\Entity;
use JsonSerializable;

class IdResponseEntity implements JsonSerializable
{


    public function __construct(private Entity $entity)
    {
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->entity->id
        ];
    }
}