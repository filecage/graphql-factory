<?php

namespace Filecage\GraphQL\Factory\Queries;

use Filecage\GraphQL\Factory\TypeTransformer\TypeTransformerInterface;

/**
 * @template T
 */
abstract class Query {

    /** @var Argument[] */
    public readonly array $arguments;

    /**
     * @param string $description
     * @param class-string<T> $returnTypeClassName
     * @param TypeTransformerInterface|null $transformer
     * @param Argument ...$arguments
     */
    function __construct (
        public readonly string $description,
        public readonly string $returnTypeClassName,
        public readonly ?TypeTransformerInterface $transformer = null,
        Argument ...$arguments,
    ) {
        $this->arguments = $arguments;
    }

    /**
     * @return T|T[]|null|(callable(): T|T[]|null)
     */
    abstract function resolve (mixed $rootValue = null, array $arguments = []) : null|object|iterable|callable;

}