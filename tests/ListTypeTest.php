<?php

namespace Filecage\GraphQL\FactoryTests;

use Filecage\GraphQL\FactoryTests\Fixtures\Queries\GetPetOwners;
use Filecage\GraphQL\FactoryTests\Fixtures\Queries\GetUsersUsingArray;
use Filecage\GraphQL\FactoryTests\Fixtures\Queries\GetUsersUsingIterators;
use Filecage\GraphQL\FactoryTests\Util\FactoryProvider;
use Filecage\GraphQL\FactoryTests\Util\MatchesGQLSnapshot;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;

class ListTypeTest extends TestCase {
    use FactoryProvider, MatchesGQLSnapshot;

    function testExpectsQueryWithListResolvedUsingArray () {
        $schema = new Schema(['query' => $this->provideFactory()->forQuery(GetUsersUsingArray::class)]);
        $result = GraphQL::executeQuery($schema, '{GetUsersUsingArray { id, type }}');

        $this->assertMatchesGraphQLSchemaSnapshot($schema);
        $this->assertMatchesQuerySnapshot($result);
    }

    function testExpectsQueryWithListResolvedUsingIterators () {
        $schema = new Schema(['query' => $this->provideFactory()->forQuery(GetUsersUsingIterators::class)]);
        $result = GraphQL::executeQuery($schema, '{GetUsersUsingIterators { id, type }}');

        $this->assertMatchesGraphQLSchemaSnapshot($schema);
        $this->assertMatchesQuerySnapshot($result);
    }

    function testExpectsQueryWithSubIteratorResolved () {
        $schema = new Schema(['query' => $this->provideFactory()->forQuery(GetPetOwners::class)]);
        $result = GraphQL::executeQuery($schema, '{GetPetOwners { person { name } pets { name } }}');

        $this->assertMatchesGraphQLSchemaSnapshot($schema);
        $this->assertMatchesQuerySnapshot($result);
    }

}