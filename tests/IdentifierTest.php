<?php

namespace Filecage\GraphQL\FactoryTests;

use Filecage\GraphQL\Factory\Exceptions\InvalidTypeException;
use Filecage\GraphQL\FactoryTests\Fixtures\Queries\GetCarWithScalarId;
use Filecage\GraphQL\FactoryTests\Fixtures\Queries\GetCarWithStringableId;
use Filecage\GraphQL\FactoryTests\Fixtures\Queries\GetCarWithStringableIdInMethod;
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

    function testExpectsQueryResultWithScalarId () {
        $schema = new Schema(['query' => $this->provideFactory()->forQuery(GetCarWithScalarId::class)]);
        $result = GraphQL::executeQuery($schema, '{scirocco: GetCarWithScalarId (id: "1") { id, name }, c43: GetCarWithScalarId (id: "2") { id, name }}')
            ->setErrorFormatter(fn(Error $error) => ['message' => $error->getMessage()]);

        $this->assertMatchesGraphQLSchemaSnapshot($schema);
        $this->assertMatchesSnapshot($result, new JsonDriver());
    }

    function testExpectsQueryResultWithStringableId () {
        $schema = new Schema(['query' => $this->provideFactory()->forQuery(GetCarWithStringableId::class)]);
        $result = GraphQL::executeQuery($schema, '{scirocco: GetCarWithStringableId (id: "1") { id, name }, c43: GetCarWithStringableId (id: "2") { id, name }}')
            ->setErrorFormatter(fn(Error $error) => ['message' => $error->getMessage()]);

        $this->assertMatchesGraphQLSchemaSnapshot($schema);
        $this->assertMatchesSnapshot($result, new JsonDriver());
    }

    function testExpectsQueryResultWithStringableIdInMethod () {
        $schema = new Schema(['query' => $this->provideFactory()->forQuery(GetCarWithStringableIdInMethod::class)]);
        $result = GraphQL::executeQuery($schema, '{scirocco: GetCarWithStringableIdInMethod (id: "1") { id, name }, c43: GetCarWithStringableIdInMethod (id: "2") { id, name }}')
            ->setErrorFormatter(fn(Error $error) => ['message' => $error->getMessage()]);

        $this->assertMatchesGraphQLSchemaSnapshot($schema);
        $this->assertMatchesSnapshot($result, new JsonDriver());
    }

    function testExpectsExceptionWhenUsingNonIDTypeAsIdentifier () {
        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Can not use property `Filecage\GraphQL\FactoryTests\Fixtures\Types\InvalidIdentifierType::$identifier` as Identifier: ID types must be string, int or an object implementing `\Stringable`');

        $this->provideFactory()->forType(InvalidIdentifierType::class);
    }

}