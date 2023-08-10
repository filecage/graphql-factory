<?php

namespace Filecage\GraphQL\FactoryTests;

use Filecage\GraphQL\FactoryTests\Fixtures\Queries\GetUser;
use Filecage\GraphQL\FactoryTests\Util\FactoryProvider;
use Filecage\GraphQL\FactoryTests\Util\MatchesGQLSnapshot;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;

class QueryTypeTest extends TestCase {
    use FactoryProvider, MatchesGQLSnapshot;

    function testExpectsQuerySchemaFromReadmeToMatchSnapshot () {
        $schema = new Schema(['query' => $this->provideFactory()->forQuery(GetUser::class)]);
        $this->assertMatchesGraphQLSchemaSnapshot($schema);
    }

}