<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Queries;

use Filecage\GraphQL\Factory\Queries\Argument;
use Filecage\GraphQL\Factory\Queries\Query;
use Filecage\GraphQL\Factory\TypeTransformer\IterableTypeTransformer;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\ArbitraryIterator;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Person;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\User;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\UserType;

class GetUsersUsingIterators extends Query {

    function __construct() {
        parent::__construct(
            description: 'Allows listing all users and uses an iterator internally',
            returnTypeClassName: User::class,
            transformer: new IterableTypeTransformer()
        );
    }

    function resolve(mixed $rootValue = null, array $arguments = []) : ArbitraryIterator {
        return new ArbitraryIterator(GetUsersUsingArray::getUsersList());
    }
}