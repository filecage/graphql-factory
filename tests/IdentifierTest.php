<?php

namespace Filecage\GraphQL\FactoryTests;

use Filecage\GraphQL\Factory\Exceptions\InvalidTypeException;
use Filecage\GraphQL\FactoryTests\Fixtures\Queries\GetCar;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\InvalidIdentifierType;
use Filecage\GraphQL\FactoryTests\Util\FactoryProvider;
use Filecage\GraphQL\FactoryTests\Util\MatchesGQLSnapshot;
use GraphQL\Error\Error;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\Drivers\JsonDriver;

class IdentifierTest extends TestCase {

    use FactoryProvider, MatchesGQLSnapshot;

    function testExpectsQueryResultWithID () {
        $schema = new Schema(['query' => $this->provideFactory()->forQuery(GetCar::class)]);
        $result = GraphQL::executeQuery($schema, '{scirocco: GetCar (id: "1") { id, name }, c43: GetCar (id: "2") { id, name }}')
            ->setErrorFormatter(fn(Error $error) => ['message' => $error->getMessage()]);

        $this->assertMatchesGraphQLSchemaSnapshot($schema);
        $this->assertMatchesSnapshot($result, new JsonDriver());
    }

    function testExpectsExceptionWhenUsingNonIDTypeAsIdentifier () {
        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Can not use property `Filecage\GraphQL\FactoryTests\Fixtures\Types\InvalidIdentifierType::$identifier` as Identifier: ID types must be string or int');

        $this->provideFactory()->forType(InvalidIdentifierType::class);
    }

}