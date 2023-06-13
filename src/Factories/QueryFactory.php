<?php

namespace Filecage\GraphQLFactory\Factories;

use Generator;
use GraphQL\Type\Definition\ObjectType;
use Filecage\GraphQLFactory\Exceptions\InvalidTypeException;
use Filecage\GraphQLFactory\Factory;
use Filecage\GraphQLFactory\Queries\Query;
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
    function __construct (private readonly Factory $factory, callable $resolveFinalize, ReflectionClass ...$queryReflections) {
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

            yield $queryReflection->getShortName() => [
                'type' => $this->factory->forType($query->returnTypeClassName),
                'description' => $query->description,
                'resolve' => function (mixed $rootValue = null, array $arguments = []) use ($query) {
                    $resolved = $query->resolve($rootValue, $arguments);
                    if (is_callable($resolved)) {
                        $resolved = call_user_func($this->resolveFinalize, $resolved);
                    }

                    return $resolved;
                }
            ];
        }
    }

}