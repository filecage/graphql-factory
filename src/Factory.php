<?php

namespace Filecage\GraphQL\Factory;

use Filecage\GraphQL\Factory\Factories\EnumTypeFactory;
use Filecage\GraphQL\Factory\Factories\IterableObjectTypeFactory;
use Filecage\GraphQL\Factory\Interfaces\Types\Cacheable;
use GraphQL\Type\Definition\Type;
use Filecage\GraphQL\Factory\Exceptions\InvalidTypeException;
use Filecage\GraphQL\Factory\Factories\InternalClassReflection;
use Filecage\GraphQL\Factory\Factories\ObjectTypeFactory;
use Filecage\GraphQL\Factory\Factories\QueryFactory;

final class Factory {

    /**
     * @var callable
     */
    private $resolveFinalize;

    /**
     * @var array<class-string, Type>
     */
    private array $cache = [];

    /**
     * @param callable $resolveFinalize a function that accepts a callable with arbitrary parameters as first argument
     */
    function __construct (callable $resolveFinalize) {
        $this->resolveFinalize = $resolveFinalize;
    }

    function forQuery (string ...$queryClassNames) : Type {
        $queryReflections = array_map(fn(string $queryClassName) => $this->reflect($queryClassName), $queryClassNames);

        return (new QueryFactory($this, $this->resolveFinalize, ...$queryReflections))->create();
    }

    /**
     * @throws InvalidTypeException
     */
    function forType (string $className) : Type {
        if (!isset($this->cache[$className])) {
            $reflection = self::reflect($className);
            if ($reflection instanceof \ReflectionEnum) {
                $factory = new EnumTypeFactory($reflection);
            } elseif ($reflection->isInternal()) {
                $factory = new InternalClassReflection($reflection);
            } elseif ($reflection->isIterateable()) {
                $factory = new IterableObjectTypeFactory($this, $reflection);
            } else {
                $factory = new ObjectTypeFactory($this, $reflection);
            }

            $type = $factory->create();
            if ($type instanceof Cacheable) {
                // Types with overwriting cache names might result in a cache hit after we've already
                // created the type. This is not ideal for obvious reasons, but we still have to respect
                // that an instance of a type may only appear once in the whole schema
                $cacheName = $type->getCacheTypeName();
                $cachedType = $this->cache[$cacheName] ?? null;
                if ($cachedType === null) {
                    // This is the first time this type has appeared in the schema, cache this instance
                    // for later use
                    $this->cache[$cacheName] = $type;
                } else {
                    // This type has appeared previously, but not under this class name. Store a reference
                    // in the cache with the requested class name and then exit early with the previously
                    // cached type
                    $this->cache[$className] = $cachedType;

                    return $cachedType;
                }
            }

            $this->cache[$className] = $type;
        }

        return $this->cache[$className];
    }

    /**
     * @throws InvalidTypeException
     */
    private function reflect (string $className) : \ReflectionClass {
        try {
            $reflection = new \ReflectionClass($className);

            // Convert to special types if that's what this is
            if ($reflection->isEnum()) {
                return new \ReflectionEnum($className);
            }

            return $reflection;
        } catch (\ReflectionException $e) {
            throw new InvalidTypeException("Could not reflect class `{$className}`: {$e->getMessage()}");
        }
    }

}