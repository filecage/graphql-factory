<?php

namespace Filecage\GraphQL\Factory\Enums;

use GraphQL\Type\Definition\Type;

enum ScalarType : string {
    case INT = 'Int';
    case FLOAT = 'Float';
    case STRING = 'String';
    case BOOLEAN = 'Boolean';
    case ID = 'ID';

    function toType () : Type {
        return match ($this->value) {
            Type::ID => Type::id(),
            Type::INT => Type::int(),
            Type::FLOAT => Type::float(),
            Type::STRING => Type::string(),
            Type::BOOLEAN => Type::boolean(),
        };
    }
}