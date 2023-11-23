<?php

namespace Filecage\GraphQL\FactoryTests;

use Filecage\GraphQL\FactoryTests\Fixtures\Queries\GetPerson;
use Filecage\GraphQL\FactoryTests\Util\FactoryProvider;
use Filecage\GraphQL\FactoryTests\Util\MatchesGQLSnapshot;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\Drivers\JsonDriver;

class ArgumentExplosionTest extends TestCase {

    use FactoryProvider, MatchesGQLSnapshot;

    function testExpectsEnumToBeResolved () {
        $schema = new Schema(['query' => $this->provideFactory()->forQuery(GetPerson::class)]);
        $result = GraphQL::executeQuery($schema, '{normalPerson: GetPerson (userId: 1) { name }, adminPerson: GetPerson (userId: 2) { name }}');

        $this->assertMatchesSnapshot($result, new JsonDriver());
    }
}