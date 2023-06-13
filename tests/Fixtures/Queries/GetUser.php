<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Queries;

use Filecage\GraphQL\Factory\Queries\Argument;
use Filecage\GraphQL\Factory\Queries\Query;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Person;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\User;
use GraphQL\Type\Definition\Type;

class GetUser extends Query {
    private const USERS = [
        1 => 'David',
        2 => 'Also David, but better'
    ];

    function __construct() {
        parent::__construct(
            description: 'Allows loading the only user we know about',
            returnTypeClassName: User::class,
            arguments: new Argument(
                description: "The user's ID", name: 'id', type: Type::int()
            )
        );
    }

    function resolve(mixed $rootValue = null, array $arguments = []) : ?User {
        $user = self::USERS[$arguments['id']] ?? null;
        if ($user === null) {
            return null;
        }

        return new User($arguments['id'], new Person($user));
    }
}