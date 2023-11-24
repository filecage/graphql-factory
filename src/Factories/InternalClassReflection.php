<?php

namespace Filecage\GraphQL\Factory\Factories;

use Filecage\GraphQL\Factory\Types\DateTimeInterfaceType;
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
        if ($this->reflectionClass->implementsInterface(\DateTimeInterface::class)) {
            return new DateTimeInterfaceType();
        }

        return match ($this->reflectionClass->name) {
            default => throw new InvalidTypeException("Unsupported internal type `{$this->reflectionClass->name}`")
        };
    }

}