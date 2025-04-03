<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Queries;

use Filecage\GraphQL\Factory\Queries\Query;
use Filecage\GraphQL\FactoryTests\Fixtures\Arguments\UserByIdArgument;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Person;

class GetPerson extends Query {

    function __construct() {
        parent::__construct(
            description: 'Allows loading the only user we know about',
            returnTypeClassName: Person::class,
            arguments: new UserByIdArgument()
        );
    }

    function resolve(mixed $rootValue = null, array $arguments = []) : ?Person {
        return $arguments['user']?->person;
    }
}