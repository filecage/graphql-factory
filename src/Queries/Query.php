<?php

namespace Filecage\GraphQL\Factory\Queries;

use Filecage\GraphQL\Factory\TypeTransformer\TypeTransformerInterface;
use GraphQL\Type\Definition\Type;

/**
 * @template T
 */
abstract class Query {

    /** @var Argument[] */
    public readonly array $arguments;

    /**
     * @param string $description
     * @param class-string<T>|Type $returnType Either a GQL type, a class-string to an actual type or a class-string to a type promise
     * @param TypeTransformerInterface|null $transformer
     * @param Argument ...$arguments
     */
    function __construct (
        public readonly string $description,
        public readonly string|Type $returnType,
        public readonly ?TypeTransformerInterface $transformer = null,
        Argument ...$arguments,
    ) {
        $this->arguments = $arguments;
    }

    /**
     * @return T|T[]|null|(callable(): T|T[]|null)|scalar
     */
    abstract function resolve (mixed $rootValue = null, array $arguments = []) : mixed;

}