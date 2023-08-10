<?php

namespace Filecage\GraphQL\FactoryTests;

use Filecage\GraphQL\FactoryTests\Fixtures\Queries\GetUser;
use Filecage\GraphQL\FactoryTests\Util\FactoryProvider;
use Filecage\GraphQL\FactoryTests\Util\MatchesGQLSnapshot;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\Drivers\JsonDriver;

class QueryTypeTest extends TestCase {
    use FactoryProvider, MatchesGQLSnapshot;

    function testExpectsQuerySchemaFromReadmeToMatchSnapshot () {
        $schema = new Schema(['query' => $this->provideFactory()->forQuery(GetUser::class)]);
        $this->assertMatchesGraphQLSchemaSnapshot($schema);
    }


    function testExpectsQuerySchemaWithPublicGetterMethodToBeResolved () {
        $schema = new Schema(['query' => $this->provideFactory()->forQuery(GetUser::class)]);
        $result = GraphQL::executeQuery($schema, '{GetUser (id: 1) { person { name, nameHashed } } }');

        $this->assertMatchesSnapshot($result, new JsonDriver());
    }

}