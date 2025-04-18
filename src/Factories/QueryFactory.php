<?php

namespace Filecage\GraphQL\Factory\Factories;

use Filecage\GraphQL\Factory\Exceptions\GraphQLFactoryException;
use Filecage\GraphQL\Factory\Interfaces\Arguments\Resolvable;
use Filecage\GraphQL\Factory\Interfaces\TypePromise;
use Filecage\GraphQL\Factory\Queries\Argument;
use Filecage\GraphQL\Factory\Queries\ArgumentType;
use Generator;
use GraphQL\Type\Definition\ObjectType;
use Filecage\GraphQL\Factory\Exceptions\InvalidTypeException;
use Filecage\GraphQL\Factory\Factory;
use Filecage\GraphQL\Factory\Queries\Query;
use GraphQL\Type\Definition\Type;
use ReflectionClass;
use ReflectionException;

/**
 * @internal
 */
final class QueryFactory implements TypeFactory {

    /** @var callable */
    private $resolveFinalize;

    /** @var ReflectionClass[]  */
    private array $queryReflections;

    /**
     * @throws InvalidTypeException
     */
    function __construct (private readonly Factory $factory, private readonly Cache $cache, callable $resolveFinalize, ReflectionClass ...$queryReflections) {
        foreach ($queryReflections as $queryReflection) {
            if (!$queryReflection->isSubclassOf(Query::class)) {
                throw new InvalidTypeException("Invalid query class `{$queryReflection->name}`: class must be of type " . Query::class);
            }
        }

        $this->resolveFinalize = $resolveFinalize;
        $this->queryReflections = $queryReflections;
    }

    /**
     * @throws InvalidTypeException
     */
    function create (): ObjectType {
        return new ObjectType([
            'name' => 'Query',
            'fields' => [...$this->generateQueryFields()]
        ]);
    }

    /**
     * @throws InvalidTypeException
     */
    private function generateQueryFields () : Generator {
        foreach ($this->queryReflections as $queryReflection) {
            try {
                // TODO: This doesn't scale well in applications with a lot of queries but is required to retrieve the attributes and the description
                /** @var Query $query */
                $query = $queryReflection->newInstance();
            } catch (ReflectionException $e) {
                throw new InvalidTypeException("Failed to create instance for query `{$queryReflection->name}`: {$e->getMessage()}");
            }

            $type = $this->factory->forType($query->returnTypeClassName);
            if ($query->transformer) {
                $type = $query->transformer->transform($type);
            }

            yield $queryReflection->getShortName() => [
                'type' => $type,
                'description' => $query->description,
                'args' => [...$this->generateArguments($query)],
                'resolve' => function (mixed $rootValue = null, array $arguments = []) use ($query) {
                    $arguments = $this->explodeArguments($arguments, ...$query->arguments);
                    $resolved = $query->resolve($rootValue, $arguments);
                    if (is_callable($resolved)) {
                        $resolved = call_user_func($this->resolveFinalize, $resolved);
                    }

                    return $resolved;
                }
            ];
        }
    }

    /**
     * @throws InvalidTypeException
     */
    private function generateArguments (Query $query) : Generator {
        foreach ($query->arguments as $argument) {
            yield $argument->name => [
                'type' => $this->createArgumentType($argument->type),
                'description' => $argument->description,
            ];
        }
    }

    /**
     * @throws InvalidTypeException
     */
    private function createArgumentType (Type|TypePromise|ArgumentType $argumentType) : Type {
        if ($argumentType instanceof Type) {
            return $argumentType;
        }

        if ($argumentType instanceof TypePromise) {
            $typePromiseClassName = get_class($argumentType);
            if (!$this->cache->has($typePromiseClassName)) {
                $this->cache->set($typePromiseClassName, $argumentType::resolveType($this->factory));
            }

            return $this->cache->get($typePromiseClassName);
        }

        $type = $this->factory->forType($argumentType->typeClassName);

        // See if we need to transform the argument's type
        $transformer = $argumentType->transformer;
        if ($transformer !== null) {
            $type = $transformer->transform($type);
        }

        return $type;
    }

    /**
     * @throws GraphQLFactoryException
     */
    private function explodeArguments (array $argumentsData, Argument ...$arguments) : array {
        foreach ($arguments as $argument) {
            if (!$argument instanceof Resolvable) {
                continue;
            }

            $resolved = $argument->resolve(null, $argumentsData);
            if (is_callable($resolved)) {
                $resolved = call_user_func($this->resolveFinalize, $resolved);
            }

            if (!is_iterable($resolved)) {
                $fixtureClassName = get_class($argument);
                throw new GraphQLFactoryException("Argument Explosion for argument `{$argument->name}` is invalid: resolver must return `iterable` or `(callable: iterable)` (defined in fixture class {$fixtureClassName})");
            }

            foreach ($resolved as $key => $value) {
                $argumentsData[$key] = $value;
            }
        }

        return $argumentsData;
    }

}