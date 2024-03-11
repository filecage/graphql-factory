<?php

namespace Filecage\GraphQL\Factory\Queries;

use GraphQL\Type\Definition\Type;

class Argument {

    function __construct (
        public readonly string $description,
        public readonly string $name,
        public readonly Type|ArgumentType $type,
    ) {}
    
}