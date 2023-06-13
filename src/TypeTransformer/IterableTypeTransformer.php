<?php

namespace Filecage\GraphQLFactory\TypeTransformer;

use GraphQL\Type\Definition\Type;

final class IterableTypeTransformer implements TypeTransformerInterface {
    function transform (Type $type): Type {
        return Type::listOf($type);
    }
}