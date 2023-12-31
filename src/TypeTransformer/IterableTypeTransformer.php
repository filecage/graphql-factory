<?php

namespace Filecage\GraphQL\Factory\TypeTransformer;

use GraphQL\Type\Definition\NullableType;
use GraphQL\Type\Definition\Type;

final class IterableTypeTransformer implements TypeTransformerInterface {

    function __construct (
        private readonly bool $allowNullValues = false
    ) {}

    function transform (Type $type): Type {
        if (!$this->allowNullValues && $type instanceof NullableType) {
            /** @var NullableType&Type $type Somehow PHPStorm is unable to infer this (v2023.3.1) */
            $type = Type::nonNull($type);
        }

        return Type::listOf($type);
    }
}