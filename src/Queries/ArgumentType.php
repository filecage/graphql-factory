<?php

namespace Filecage\GraphQL\Factory\Queries;

use Filecage\GraphQL\Factory\TypeTransformer\TypeTransformerInterface;

final class ArgumentType {
    function __construct (
        public readonly string $typeClassName,
        public readonly ?TypeTransformerInterface $transformer = null
    ) {}
}