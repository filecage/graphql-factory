<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Types\InputTypes;

use Filecage\GraphQL\Factory\Factory;
use Filecage\GraphQL\Factory\Interfaces\TypePromise;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\UserType;
use GraphQL\Type\Definition\InputObjectType;

final class SomeSubtypeInput implements TypePromise {
    static function resolveType (Factory $factory): InputObjectType {
        return new InputObjectType([
            'name' => 'SomeSubtypeInput',
            'fields' => [
                'alsoUserType' => $factory->forType(UserType::class)
            ]
        ]);
    }
}