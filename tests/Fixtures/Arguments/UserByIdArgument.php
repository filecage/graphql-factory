<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Arguments;

use Filecage\GraphQL\Factory\Interfaces\Arguments\Resolvable;
use Filecage\GraphQL\Factory\Queries\Argument;
use Filecage\GraphQL\FactoryTests\Fixtures\Queries\GetUser;
use GraphQL\Type\Definition\Type;

final class UserByIdArgument extends Argument implements Resolvable {

    function __construct () {
        parent::__construct(
            description: "The user's ID", name: 'userId', type: Type::int()
        );
    }

    function resolve (mixed $rootValue = null, array $arguments = []): \Generator {
        $userGetter = new GetUser();
        yield 'user' => $userGetter->resolve(null, ['id' => $arguments['userId']]);
    }
}