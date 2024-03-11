<?php

namespace Filecage\GraphQL\Factory\TypeTransformer;

use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\NullableType;
use GraphQL\Type\Definition\Type;

final class NonNullTypeTransformer implements TypeTransformerInterface {
    function transform (Type $type): Type {
        if ($type instanceof NonNull) {
            return $type;
        }

        /** @var NullableType&Type $type Somehow PHPStorm is unable to infer this (v2023.3.4) */
        return Type::nonNull($type);
    }
}