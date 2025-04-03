<?php

namespace Filecage\GraphQL\Factory\Factories;

use Filecage\GraphQL\Annotations\Attributes\Contains;
use Filecage\GraphQL\Annotations\Attributes\Identifier;
use Filecage\GraphQL\Annotations\Attributes\Ignore;
use Filecage\GraphQL\Annotations\Attributes\Promote;
use Filecage\GraphQL\Annotations\Attributes\TypeAlias;
use Filecage\GraphQL\Annotations\Attributes\EmptyType;
use Filecage\GraphQL\Annotations\Enums\ScalarType;
use GraphQL\Type\Definition\Description;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Filecage\GraphQL\Factory\Exceptions\InvalidTypeException;
use Filecage\GraphQL\Factory\Factory;
use GraphQL\Type\Definition\UnionType;
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
        private readonly Cache $cache,
        private readonly \ReflectionClass $reflectionClass
    ) {}

    function create () : Type {
        return new ObjectType([
            'name' => $this->reflectionClass->getShortName(),
            'fields' => [...$this->generateFields()],
        ]);
    }

    /**
     * @throws InvalidTypeException
     */
    private function generateFields () : \Generator {
        if ($this->reflectionClass->isInternal()) {
            throw new InvalidTypeException("Unsupported internal class `{$this->reflectionClass->name}`");
        }

        if (!empty($this->reflectionClass->getAttributes(EmptyType::class))) {
            // Yield dummy boolean field `_`, then return (so void always has no outputs)
            yield '_' => [
                'type' => Type::boolean(),
                'description' => 'Dummy value for empty object type. Do not query.',
            ];
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
        if ($type instanceof \ReflectionUnionType) {
            return $this->mapUnionType($type, $context);
        }

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
        if (count($contains) < 1) {
            throw new InvalidTypeException("Type clarification for array type is missing (expected at least one `Contains` attribute) for {$this->formatExceptionContext($context)}");
        }

        return $this->mapTypeForContains($context, ...array_map(fn(\ReflectionAttribute $contains) => $contains->newInstance(), $contains));
    }

    /**
     * @throws InvalidTypeException
     */
    protected function mapTypeForContains (\ReflectionMethod|\ReflectionProperty|\ReflectionClass $context, Contains ...$contains) : Type {
        if (count($contains) === 1) {
            if (is_string($contains[0]->type)) {
                return Type::listOf($this->wrapAllowsNull($contains[0]->allowsNull, $this->factory->forType($contains[0]->type)));
            }

            return Type::listOf($this->wrapAllowsNull($contains[0]->allowsNull, $this->mapScalarTypeToGraphQLType($contains[0]->type)));
        }

        // Union Type Contains, yay, fun!
        $typeName = $this->findTypeAlias($context)->getTypeAlias();
        // Only object types are allowed in a union type
        $classNames = array_map(fn(Contains $contains) => is_string($contains->type) ? $contains->type : throw new InvalidTypeException(
            "Unsupported union type: Union types in GraphQL can only contain named class/object references, but got scalar/unnamed for {$this->formatExceptionContext($context)}"
        ), $contains);

        $signature = $this->getUnionSignatureFromContains(...$contains);
        if (!$this->hasUnionTypeCached($typeName, $signature, $context)) {
            $this->cache->setUnion($typeName, $this->createUnionType($typeName, ...$classNames), $signature);
        }

        return Type::listOf($this->wrapAllowsNull(false, $this->cache->getUnionType($typeName)));
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

    /**
     * @throws InvalidTypeException
     */
    private function mapUnionType (\ReflectionUnionType $type, \ReflectionMethod|\ReflectionProperty $context) : MappedType {
        $typeName = $this->findTypeAlias($context)->getTypeAlias();
        $signature = $this->getUnionSignatureFromReflectionUnionType($type);

        if (!$this->hasUnionTypeCached($typeName, $signature, $context)) {
            $classNames = array_map(function(\ReflectionType $type) use ($context) {
                if (!$type instanceof \ReflectionNamedType || $type->isBuiltin()) {
                    throw new InvalidTypeException("Unsupported union type: Union types in GraphQL can only contain named class/object references, but got scalar/unnamed for {$this->formatExceptionContext($context)}");
                }

                return $type->getName();
            }, $type->getTypes());

            $this->cache->setUnion($typeName, $this->createUnionType($typeName, ...$classNames), $signature);
        }

        return new MappedType($this->wrapAllowsNull($type->allowsNull(), $this->cache->getUnionType($typeName)));
    }

    /**
     * @param string $typeName
     * @param string ...$classNames
     *
     * @return UnionType
     * @throws InvalidTypeException
     */
    private function createUnionType (string $typeName, string ...$classNames) : UnionType {
        $objectTypes = array_map(fn(string $className) => $this->factory->forType($className), array_combine($classNames, $classNames));

        return new UnionType([
            'name' => $typeName,
            'types' => $objectTypes,
            'resolveType' => fn (object $value) : ?ObjectType => $objectTypes[get_class($value)] ?? null,
        ]);
    }

    /**
     * @throws InvalidTypeException
     */
    private function hasUnionTypeCached (string $typeName, string $signature, \ReflectionProperty|\ReflectionMethod|\ReflectionClass $context) : bool {
        if (!$this->cache->hasUnion($typeName)) {
            return false;
        }

        if ($this->cache->getUnionSignature($typeName) !== $signature) {
            throw new InvalidTypeException("Unsupported union type: A previously defined type alias `{$typeName}` is different to the one of {$this->formatExceptionContext($context)}");
        }

        return true;
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

    /**
     * @throws InvalidTypeException
     */
    private function findTypeAlias (\ReflectionMethod|\ReflectionProperty|\ReflectionClass $typeAliasAware) : TypeAlias {
        /** @var \ReflectionAttribute|null $typeAliasAttribute */
        $typeAliasAttribute = $typeAliasAware->getAttributes(TypeAlias::class)[0] ?? null;
        if ($typeAliasAttribute === null) {
            throw new InvalidTypeException("Missing union type `TypeAlias` attribute declaration for {$this->formatExceptionContext($typeAliasAware)}");
        }

        return $typeAliasAttribute->newInstance();
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

    private function getUnionSignatureFromReflectionUnionType (\ReflectionUnionType $type) : string {
        $signature = array_filter(array_map(fn(\ReflectionNamedType|\ReflectionIntersectionType $type) => ($type->isBuiltin() && $type->getName() === 'null') ? null : $type->getName(), $type->getTypes()));

        return $this->hashUnionSignature($signature);
    }

    private function getUnionSignatureFromContains (Contains ...$contains) : string {
        $signature = array_filter(array_map(fn(Contains $contains) => $contains->type, $contains));

        return $this->hashUnionSignature($signature);
    }

    private function hashUnionSignature (array $signatureBits) : string {
        sort($signatureBits);

        return hash('xxh3', join('|', $signatureBits));
    }

    private function formatExceptionContext (\ReflectionMethod|\ReflectionProperty|\ReflectionClass $context) : string {
        if ($context instanceof \ReflectionClass) {
            return "class `{$context->name}`";
        }

        if ($context instanceof \ReflectionMethod) {
            return "return type of `{$context->class}::{$context->name}()`";
        }

        return "property `{$context->class}::\${$context->name}`";
    }

}