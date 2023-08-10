<?php

namespace Filecage\GraphQL\FactoryTests;

use Filecage\GraphQL\FactoryTests\Fixtures\Queries\GetUser;
use Filecage\GraphQL\FactoryTests\Util\FactoryProvider;
use Filecage\GraphQL\FactoryTests\Util\MatchesGQLSnapshot;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\Drivers\JsonDriver;

class EnumTypeTest extends TestCase {
    use FactoryProvider, MatchesGQLSnapshot;
    function testExpectsEnumToBeResolved () {
        $schema = new Schema(['query' => $this->provideFactory()->forQuery(GetUser::class)]);
        $result = GraphQL::executeQuery($schema, '{normalUser: GetUser (id: 1) { type }, adminUser: GetUser (id: 2) { type }}');

        $this->assertMatchesSnapshot($result, new JsonDriver());
    }

}