<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Queries;

use Filecage\GraphQL\Factory\Queries\Argument;
use Filecage\GraphQL\Factory\Queries\Query;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Person;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\User;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\UserType;
use GraphQL\Type\Definition\Type;

class GetUser extends Query {

    private const USERS = [
        1 => ['name' => 'David', 'type' => UserType::NormalUser],
        2 => ['name' => 'Also David, but better', 'type' => UserType::Admin],
    ];

    function __construct() {
        parent::__construct(
            description: 'Allows loading the only user we know about',
            returnTypeClassName: User::class,
            arguments: new Argument(
                description: "The user's ID", name: 'id', type: Type::id()
            )
        );
    }

    function resolve(mixed $rootValue = null, array $arguments = []) : ?User {
        ['name' => $name, 'type' => $type] = self::USERS[$arguments['id']] ?? null;
        if ($name === null || $type === null) {
            return null;
        }

        return new User($arguments['id'], new Person($name), $type);
    }
}