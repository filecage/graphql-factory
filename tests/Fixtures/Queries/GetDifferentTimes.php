<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Queries;

use Filecage\GraphQL\Factory\Queries\Query;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\TwoDifferentTimes;

class GetDifferentTimes extends Query {

    function __construct() {
        parent::__construct(
            description: 'Returns two different times',
            returnTypeClassName: TwoDifferentTimes::class
        );
    }
    function resolve(mixed $rootValue = null, array $arguments = []) : ?TwoDifferentTimes {
        // This is actually never called but required for a test where multiple DateTimeInterfaces exist in the schema
        return new TwoDifferentTimes(null, null);
    }
}