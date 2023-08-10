<?php

namespace Filecage\GraphQL\Factory\Factories;

use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\PhpEnumType;

final class EnumTypeFactory implements TypeFactory {

    function __construct (private readonly \ReflectionEnum $reflectionEnum) {}

    function create (): EnumType {
        // TODO: This is a double reflection (because `PhpEnumType` creates a new reflection of the enum internally)
        return new PhpEnumType($this->reflectionEnum->name);
    }

}