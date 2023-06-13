<?php

namespace Filecage\GraphQLFactory\Queries;

/**
 * @template T
 */
abstract class Query {

    /** @var Argument[] */
    public readonly array $arguments;

    /**
     * @param class-string<T> $returnTypeClassName
     * @param string $description
     */
    function __construct (
        public readonly string $description,
        public readonly string $returnTypeClassName,
        Argument ...$arguments
    ) {
        $this->arguments = $arguments;
    }

    /**
     * @return T|null|(callable(): T|null)
     */
    abstract function resolve (mixed $rootValue = null, array $arguments = []) : null|object|callable;

}