<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Types\InputTypes;

use Filecage\GraphQL\Factory\Factory;
use Filecage\GraphQL\Factory\Interfaces\TypePromise;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\UserType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

final class UserMutation implements TypePromise {
    static function resolveType (Factory $factory): Type {
        return new InputObjectType([
            'name' => 'UserMutation',
            'fields' => [
                'name' => [
                    'type' => Type::nonNull(Type::string()),
                ],
                'type' => [
                    'type' => Type::nonNull($factory->forType(UserType::class)),
                ],
                'additionally' => [
                    'type' => $factory->forType(SomeSubtypeInput::class),
                    'description' => 'This is to test a promise type within a promise',
                ]
            ],
        ]);
    }
}