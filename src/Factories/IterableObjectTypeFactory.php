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

    /**
     * @throws InvalidTypeException
     */
    function create (): Type {
        $contains = $this->reflectionClass->getAttributes(Contains::class);
        if (empty($contains)) {
            return parent::create();
        }

        return $this->mapTypeForContains($this->reflectionClass, ...array_map(fn(\ReflectionAttribute $contains) => $contains->newInstance(), $contains));
    }
}