<?php

namespace Filecage\GraphQL\Factory\Factories;

use GraphQL\Type\Definition\Type;
use Filecage\GraphQL\Factory\Exceptions\InvalidTypeException;

/**
 * @internal
 */
final class InternalClassReflection implements TypeFactory {

    function __construct (private readonly \ReflectionClass $reflectionClass) {}

    /**
     * @throws InvalidTypeException
     */
    function create () : Type {
        return match ($this->reflectionClass->name) {
            \DateTimeImmutable::class => Type::string(),
            default => throw new InvalidTypeException("Unsupported internal type `{$this->reflectionClass->name}`")
        };
    }

}