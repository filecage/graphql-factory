<?php

namespace Filecage\GraphQLFactory\TypeTransformer;

use GraphQL\Type\Definition\Type;

/**
 * @internal
 */
interface TypeTransformerInterface {

    function transform (Type $type) : Type;

}