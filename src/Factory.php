<?php

namespace Filecage\GraphQL\Factory;

use Filecage\GraphQL\Factory\Factories\EnumTypeFactory;
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
            } else {
                $factory = new ObjectTypeFactory($this, $reflection);
            }

            $this->cache[$className] = $factory->create();
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