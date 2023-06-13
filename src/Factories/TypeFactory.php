<?php

namespace Filecage\GraphQLFactory\Factories;

use GraphQL\Type\Definition\Type;

/**
 * @internal
 */
interface TypeFactory {

    function create () : Type;

}