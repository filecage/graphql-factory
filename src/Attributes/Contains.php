<?php

namespace Filecage\GraphQLFactory\Attributes;

use Filecage\GraphQLFactory\Enums\ScalarType;

#[\Attribute]
final class Contains {

    /**
     * Passing multiple types turns this into a union type
     * Passing null in the list of types controls the `allowNull` wrap type
     *
     * @param ScalarType|class-string $type
     * @param bool $allowsNull
     */
    function __construct (public readonly ScalarType|string $type, public readonly bool $allowsNull = false) {}

}