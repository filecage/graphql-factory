<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Mutations;

use Filecage\GraphQL\Factory\Queries\Argument;
use Filecage\GraphQL\Factory\Queries\Query;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\InputTypes\UserMutation;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Person;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\User;
use GraphQL\Type\Definition\Type;

class SetUser extends Query {
    function __construct() {
        parent::__construct(
            'A mutation that changes a user',
            User::class,
            null,
            new Argument(description: "The user's ID", name: 'id', type: Type::id()),
            new Argument(description: "The new user settings", name: 'user', type: new UserMutation())
        );
    }

    function resolve(mixed $rootValue = null, array $arguments = []) : ?User {
        return new User($arguments['id'], new Person($arguments['user']['name']), $arguments['user']['type']);
    }
}