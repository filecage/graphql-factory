<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Queries;

use Filecage\GraphQL\Factory\Queries\Query;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Something;

class GetSomething extends Query {

    function __construct() {
        parent::__construct(
            description: 'Allows loading something, which is actually an empty object',
            returnTypeClassName: Something::class,
        );
    }

    function resolve(mixed $rootValue = null, array $arguments = []) : Something {
        return new Something();
    }
}