<?php

namespace Filecage\GraphQL\Factory\TypeTransformer;

use GraphQL\Type\Definition\Type;

final class TypeTransformerCollection implements TypeTransformerInterface {
    private readonly array $typeTransformers;
    function __construct (TypeTransformerInterface ...$typeTransformer) {
        $this->typeTransformers = $typeTransformer;
    }

    function transform (Type $type): Type {
        foreach ($this->typeTransformers as $typeTransformer) {
            $type = $typeTransformer->transform($type);
        }

        return $type;
    }
}