<?php

namespace Filecage\GraphQL\Factory\Factories;

use Filecage\GraphQL\Annotations\Attributes\Contains;
use Filecage\GraphQL\Factory\Exceptions\InvalidTypeException;
use Filecage\GraphQL\Factory\Factory;
use GraphQL\Type\Definition\Type;

/**
 * @internal
 */
final class IterableObjectTypeFactory extends ObjectTypeFactory {

    function __construct(
        Factory $factory,
        Cache $cache,
        private readonly \ReflectionClass $reflectionClass,
    ) {
        parent::__construct($factory, $cache, $this->reflectionClass);
    }

    function create (): Type {
        $contains = $this->reflectionClass->getAttributes(Contains::class);
        if (empty($contains)) {
            return parent::create();
        }

        if (count($contains) > 1) {
            throw new InvalidTypeException("Type clarification is too ambiguous (expected none or exactly 1 `Contains` attribute) for iterator `{{$this->reflectionClass->getName()}}`");
        }

        return $this->mapTypeForContains($contains[0]->newInstance());
    }
}