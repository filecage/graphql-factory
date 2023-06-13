<?php

namespace Filecage\GraphQL\FactoryTests;

use Filecage\GraphQL\Factory\Factory;
use Filecage\GraphQL\FactoryTests\Fixtures\Queries\GetUser;
use Filecage\GraphQL\FactoryTests\Util\FactoryProvider;
use Filecage\GraphQL\FactoryTests\Util\MatchesGQLSnapshot;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;

class QueryTypeTest extends TestCase {
    use FactoryProvider, MatchesGQLSnapshot;
    private Factory $factory;

    function setUp (): void {
        $this->factory = new Factory(fn() => null);
    }

    function testExpectsQuerySchemaFromReadmeToMatchSnapshot () {
        $schema = new Schema(['query' => $this->factory->forQuery(GetUser::class)]);
        $this->assertMatchesGraphQLSchemaSnapshot($schema);
    }

}