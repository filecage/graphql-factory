<?php

namespace Filecage\GraphQL\Factory\Interfaces\Arguments;

use Generator;

interface Resolvable {

    /**
     * @template T of Generator<string, mixed>
     * @param mixed|null $rootValue
     * @param array $arguments
     *
     * @return T|(callable: T)
     */
    function resolve(mixed $rootValue = null, array $arguments = []) : callable|Generator;

}