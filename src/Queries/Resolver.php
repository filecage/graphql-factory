<?php

namespace Filecage\GraphQLFactory\Queries;
abstract class Resolver {

    /** @var Argument[] */
    public readonly array $arguments;

    function __construct (Argument ...$arguments) {
        $this->arguments = $arguments;
    }

    abstract function resolve (mixed $rootValue = null, array $arguments = []);

}