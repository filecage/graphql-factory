<?php

namespace Filecage\GraphQL\Factory\Queries;

use Filecage\GraphQL\Factory\Interfaces\TypePromise;
use GraphQL\Type\Definition\Type;

class Argument {

    function __construct (
        public readonly string $description,
        public readonly string $name,
        public readonly Type|ArgumentType|TypePromise $type,
    ) {}
    
}