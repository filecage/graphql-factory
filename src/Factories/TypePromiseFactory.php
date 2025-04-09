<?php

namespace Filecage\GraphQL\Factory\Factories;

use Filecage\GraphQL\Factory\Exceptions\GraphQLFactoryException;
use Filecage\GraphQL\Factory\Factory;
use Filecage\GraphQL\Factory\Interfaces\TypePromise;
use GraphQL\Type\Definition\Type;
use ReflectionClass;

/**
 * @internal
 */
final class TypePromiseFactory implements TypeFactory {

    function __construct (private readonly Factory $factory, private readonly ReflectionClass $typePromiseReflection) {}

    /**
     * @throws GraphQLFactoryException
     */
    function create (): Type {
        if (!$this->typePromiseReflection->implementsInterface(TypePromise::class)) {
            throw new GraphQLFactoryException("Invalid TypePromise: class `{$this->typePromiseReflection->name}` must implement " . TypePromise::class);
        }

        /** @var class-string<TypePromise> $typePromiseClassName */
        $typePromiseClassName = $this->typePromiseReflection->name;

        return $typePromiseClassName::resolveType($this->factory);
    }
}