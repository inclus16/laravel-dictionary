<?php

namespace Inclus16\LaravelDictionary\Validation\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\App;
use Inclus16\LaravelDictionary\Handlers\HandlerFactory;

readonly class NotInDictionary implements ValidationRule
{

    public function __construct(private string $slug,
                                private string $field)
    {
    }

    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        $handlerFactor = app(HandlerFactory::class);
        $handler = $handlerFactor->getHandler($this->slug);
        if ($handler->getEntities()->contains(fn($entity) => $entity->{$this->field} === $value)) {
            $fail('dictionary::validation.already_in_dictionary')
                ->translate(['value' => $value, 'slug' => $this->slug], App::currentLocale());
        }
    }
}