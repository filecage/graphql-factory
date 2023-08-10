<?php

namespace Filecage\GraphQL\Factory\Factories;

use Filecage\GraphQL\Annotations\Attributes\Contains;
use Filecage\GraphQL\Annotations\Attributes\Ignore;
use Filecage\GraphQL\Annotations\Enums\ScalarType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Filecage\GraphQL\Factory\Exceptions\InvalidTypeException;
use Filecage\GraphQL\Factory\Factory;

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
            if (!empty($property->getAttributes(Ignore::class))) {
                continue;
            }

            yield $property->name => [
                'type' => $this->mapType($property->getType(), $property)
            ];
        }

        foreach ($this->reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if (!empty($method->getAttributes(Ignore::class))) {
                continue;
            }

            if (!str_starts_with(strtolower($method->name), 'get') || strtolower($method->name) === 'get') {
                continue;
            }

            yield lcfirst(substr($method->name, 3)) => [
                'type' => $this->mapType($method->getReturnType(), $method),
                'resolve' => fn ($rootValue, array $args) => call_user_func([$rootValue, $method->name, ...$args]),
            ];
        }
    }

    /**
     * @throws InvalidTypeException
     */
    private function mapType (?\ReflectionType $type, \ReflectionMethod|\ReflectionProperty $context) : Type {
        if (!$type instanceof \ReflectionNamedType) {
            throw new InvalidTypeException("Missing or invalid return type for {$this->formatExceptionContext($context)}");
        }


        if ($type->isBuiltin()) {
            return $this->wrapAllowsNull($type->allowsNull(), match($type->getName()) {
                'string' => Type::string(),
                'float' => Type::float(),
                'int' => Type::int(),
                'bool' => Type::boolean(),
                'array' => $this->mapTypeForArray($type, $context),
                default => throw new InvalidTypeException("Unsupported builtin type `{$type->getName()}` for {$this->formatExceptionContext($context)}"),
            });
        }

        return $this->wrapAllowsNull($type->allowsNull(), $this->factory->forType($type->getName()));
    }

    /**
     * @throws InvalidTypeException
     */
    private function mapTypeForArray (\ReflectionType $type, \ReflectionMethod|\ReflectionProperty $context) : Type {
        $contains = $context->getAttributes(Contains::class);
        if (count($contains) !== 1) {
            throw new InvalidTypeException("Type clarification for array type is missing or too ambiguous (expected exactly 1 `Contains` attribute) for {$this->formatExceptionContext($context)}");
        }

        /** @var Contains $contains */
        $contains = $contains[0]->newInstance();

        if (is_string($contains->type)) {
            return Type::listOf($this->wrapAllowsNull($contains->allowsNull, $this->factory->forType($contains->type)));
        }

        return Type::listOf($this->wrapAllowsNull($contains->allowsNull, $this->mapScalarTypeToGraphQLType($contains->type)));
    }

    private function mapScalarTypeToGraphQLType (ScalarType $scalarType) : Type {
        return match ($scalarType) {
            ScalarType::ID => Type::id(),
            ScalarType::INT => Type::int(),
            ScalarType::FLOAT => Type::float(),
            ScalarType::STRING => Type::string(),
            ScalarType::BOOLEAN => Type::boolean(),
        };
    }

    private function wrapAllowsNull (bool $allowsNull, Type $type) : Type {
        return $allowsNull ? $type : Type::nonNull($type);
    }

    private function formatExceptionContext (\ReflectionMethod|\ReflectionProperty $context) : string {
        if ($context instanceof \ReflectionMethod) {
            return "return type of `{$context->class}::{$context->name}()`";
        }

        return "property `{$context->class}::\${$context->name}`";
    }

}