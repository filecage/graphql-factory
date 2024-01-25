<?php

namespace Filecage\GraphQL\Factory\Factories;

use GraphQL\Type\Definition\Type;

final class MappedType {

    /**
     * @var callable
     */
    public readonly mixed $resolveFn;

    function __construct (public readonly Type $type, callable $resolveFn = null) {
        $this->resolveFn = $resolveFn;
    }

}