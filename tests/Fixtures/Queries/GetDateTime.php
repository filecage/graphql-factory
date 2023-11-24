<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Queries;

use Filecage\GraphQL\Factory\Queries\Query;

class GetDateTime extends Query {

    function __construct() {
        parent::__construct(
            description: 'Returns the date time of 2023-11-24 12:34:56 in UTC',
            returnTypeClassName: \DateTimeInterface::class
        );
    }
    function resolve(mixed $rootValue = null, array $arguments = []) : ?\DateTimeInterface {
        return new \DateTimeImmutable('2023-11-24T12:34:56Z');
    }
}