<?php

namespace Filecage\GraphQL\Factory\Factories;

use GraphQL\Type\Definition\Type;

/**
 * @internal
 */
interface TypeFactory {

    function create () : Type;

}