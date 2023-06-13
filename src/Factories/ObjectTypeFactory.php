<?php

namespace Filecage\GraphQLFactory\Factories;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Filecage\GraphQLFactory\Exceptions\InvalidTypeException;
use Filecage\GraphQLFactory\Factory;

/**
 * @internal
 */
final class ObjectTypeFactory implements TypeFactory {

    /**
     * @internal
     */
    function __construct (
        private readonly Factory $factory,
        private readonly \ReflectionClass $reflectionClass
    ) {}

    function create () : Type {
        return new ObjectType([
            'name' => $this->reflectionClass->getShortName(),
            'fields' => [...$this->generateFields()]
        ]);
    }

    /**
     * @throws InvalidTypeException
     */
    private function generateFields () : \Generator {
        if ($this->reflectionClass->isInternal()) {
            throw new InvalidTypeException("Unsupported internal class `{$this->reflectionClass->name}`");
        }

        foreach ($this->reflectionClass->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            yield $property->name => [
                'type' => $this->mapType($property->getType(), "property `{$property->class}::\${$property->name}`")
            ];
        }

        foreach ($this->reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if (!str_starts_with(strtolower($method->name), 'get') || strtolower($method->name) === 'get') {
                continue;
            }

            yield lcfirst(substr($method->name, 3)) => [
                'type' => $this->mapType($method->getReturnType(), "method `{$method->class}::{$method->name}()`")
            ];

        }
    }

    /**
     * @throws InvalidTypeException
     */
    private function mapType (?\ReflectionType $type, string $exceptionContext) : Type {
        if (!$type instanceof \ReflectionNamedType) {
            throw new InvalidTypeException("Missing or invalid return type for $exceptionContext");
        }

        if ($type->isBuiltin()) {
            return $this->finalizeReflectionType($type, match($type->getName()) {
                'string' => Type::string(),
                'float' => Type::float(),
                'int' => Type::int(),
                'bool' => Type::boolean(),
                default => throw new InvalidTypeException("Unsupported builtin type `{$type->getName()}` for $exceptionContext"),
            });
        }

        return $this->finalizeReflectionType($type, $this->factory->forType($type->getName()));
    }

    private function finalizeReflectionType (\ReflectionType $reflectionType, Type $type) : Type {
        return $reflectionType->allowsNull() ? $type : Type::nonNull($type);
    }

}