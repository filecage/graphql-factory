<?php

namespace Filecage\GraphQL\Factory\Queries;

use GraphQL\Type\Definition\Type;

final class Argument {

    function __construct (
        public readonly string $description,
        public readonly string $name,
        public readonly Type $type,
    ) {}
    
}