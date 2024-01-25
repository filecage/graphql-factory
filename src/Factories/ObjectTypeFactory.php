<?php

namespace Filecage\GraphQL\Factory\Factories;

use Filecage\GraphQL\Annotations\Attributes\Contains;
use Filecage\GraphQL\Annotations\Attributes\Identifier;
use Filecage\GraphQL\Annotations\Attributes\Ignore;
use Filecage\GraphQL\Annotations\Attributes\Promote;
use Filecage\GraphQL\Annotations\Enums\ScalarType;
use GraphQL\Type\Definition\Description;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Filecage\GraphQL\Factory\Exceptions\InvalidTypeException;
use Filecage\GraphQL\Factory\Factory;
use SensitiveParameter;

/**
 * @internal
 */
class ObjectTypeFactory implements TypeFactory {

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
            if ($this->hasSkipAttributes($property)) {
                continue;
            }

            $type = $this->mapType($property->getType(), $property);
            yield $property->name => [
                'type' => $type->type,
                'description' => $this->findDescription($property),
                'resolve' => $type->resolveFn,
            ];
        }

        foreach ($this->reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($this->hasSkipAttributes($method) || $method->isInternal()) {
                continue;
            }

            if ((!str_starts_with(strtolower($method->name), 'get') || strtolower($method->name) === 'get') && empty($promoteAttributes = $method->getAttributes(Promote::class))) {
                continue;
            }

            if (!empty($promoteAttributes)) {
                /** @var Promote $promoteAttribute */
                $promoteAttribute = $promoteAttributes[0]->newInstance();
                $name = $promoteAttribute->name ?? $method->getName();
            } else {
                // Just remove the `get` from the string
                $name = lcfirst(substr($method->name, 3));
            }

            $type = $this->mapType($method->getReturnType(), $method);
            yield $name => [
                'type' => $type->type,
                'description' => $this->findDescription($method),
                'resolve' => fn ($rootValue, array $args) => call_user_func([$rootValue, $method->name, ...$args]),
            ];
        }
    }


    /**
     * @throws InvalidTypeException
     */
    private function mapType (?\ReflectionType $type, \ReflectionMethod|\ReflectionProperty $context) : MappedType {
        if (!$type instanceof \ReflectionNamedType) {
            throw new InvalidTypeException("Missing or invalid return type for {$this->formatExceptionContext($context)}");
        }

        if (!empty($context->getAttributes(Identifier::class))) {
            return $this->mapIdentifierType($type, $context);
        }


        if ($type->isBuiltin()) {
            return new MappedType($this->wrapAllowsNull($type->allowsNull(), match($type->getName()) {
                'string' => Type::string(),
                'float' => Type::float(),
                'int' => Type::int(),
                'bool' => Type::boolean(),
                'array' => $this->mapTypeForArray($type, $context),
                default => throw new InvalidTypeException("Unsupported builtin type `{$type->getName()}` for {$this->formatExceptionContext($context)}"),
            }));
        }

        return new MappedType($this->wrapAllowsNull($type->allowsNull(), $this->factory->forType($type->getName())));
    }

    /**
     * @throws InvalidTypeException
     */
    private function mapIdentifierType (\ReflectionNamedType $type, \ReflectionMethod|\ReflectionProperty $context) : MappedType {
        $resolveFn = null;
        if (!$type->isBuiltin() && in_array(\Stringable::class, class_implements($type->getName()))) {
            if ($context instanceof \ReflectionMethod) {
                $resolveFn = fn($rootValue, array $args) => (string) call_user_func([$rootValue, $context->name, ...$args]);
            } else {
                $resolveFn = fn($rootValue, array $args) => $rootValue->{$context->name}->__toString();
            }
        } else if (!in_array($type->getName(), ['string', 'int'])) {
            throw new InvalidTypeException("Can not use {$this->formatExceptionContext($context)} as Identifier: ID types must be string, int or an object implementing `\Stringable`");
        }

        return new MappedType($this->wrapAllowsNull($type->allowsNull(), Type::id()), $resolveFn);
    }

    /**
     * @throws InvalidTypeException
     */
    protected function mapTypeForArray (\ReflectionType $type, \ReflectionMethod|\ReflectionProperty|\ReflectionClass $context) : Type {
        $contains = $context->getAttributes(Contains::class);
        if (count($contains) !== 1) {
            throw new InvalidTypeException("Type clarification for array type is missing or too ambiguous (expected exactly 1 `Contains` attribute) for {$this->formatExceptionContext($context)}");
        }

        return $this->mapTypeForContains($contains[0]->newInstance());
    }

    protected function mapTypeForContains (Contains $contains) : Type {
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

    protected function wrapAllowsNull (bool $allowsNull, Type $type) : Type {
        return $allowsNull ? $type : Type::nonNull($type);
    }

    private function hasSkipAttributes (\ReflectionMethod|\ReflectionProperty $attributeAware) : bool {
        $skipProperties = [
            ...$attributeAware->getAttributes(Ignore::class),
        ];

        // For promoted attributes we'll have to take a look at the declaring parameter, too
        if ($attributeAware instanceof \ReflectionProperty && $attributeAware->isPromoted()) {
            /** @var \ReflectionParameter $propertyDeclaringParameter */
            $propertyDeclaringParameters = array_filter($attributeAware->getDeclaringClass()->getConstructor()->getParameters(), fn(\ReflectionParameter $parameter) => $parameter->name === $attributeAware->name);
            $propertyDeclaringParameter = array_pop($propertyDeclaringParameters);

            $skipProperties = array_merge($skipProperties, [
                ...$propertyDeclaringParameter->getAttributes(SensitiveParameter::class),
            ]);
        }

        return !empty($skipProperties);
    }

    private function findDescription (\ReflectionMethod|\ReflectionProperty $descriptionAware) : ?string {
        $descriptions = $descriptionAware->getAttributes(Description::class);
        if (empty($descriptions)) {
            return null;
        }

        /** @var Description $description */
        $description = $descriptions[0]->newInstance();

        return $description->description;
    }

    private function formatExceptionContext (\ReflectionMethod|\ReflectionProperty $context) : string {
        if ($context instanceof \ReflectionMethod) {
            return "return type of `{$context->class}::{$context->name}()`";
        }

        return "property `{$context->class}::\${$context->name}`";
    }

}