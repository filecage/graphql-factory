<?php

namespace Filecage\GraphQL\Factory\TypeTransformer;

use GraphQL\Type\Definition\Type;

/**
 * @internal
 */
interface TypeTransformerInterface {

    function transform (Type $type) : Type;

}