<?php

namespace Filecage\GraphQL\FactoryTests;

use Filecage\GraphQL\Factory\Exceptions\InvalidTypeException;
use Filecage\GraphQL\FactoryTests\Fixtures\Queries\GetFamilyMembers;
use Filecage\GraphQL\FactoryTests\Fixtures\Queries\GetPersonOrPet;
use Filecage\GraphQL\FactoryTests\Fixtures\Queries\GetPersonOrPetContainer;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\UnionTypes\ListUnionType;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\UnionTypes\ListUnionTypeContainerWithMissingContains;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\UnionTypes\ListUnionTypePropertyWithMissingContains;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\UnionTypes\ListUnionTypeReturnWithMissingContains;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\UnionTypes\UnionTypeWithMissingTypeAlias;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\UnionTypes\UnionTypeWithMultipleTypeAliasAndDifferentSignature;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\UnionTypes\UnionTypeWithSameSignatureButNullableAndNonNullable;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\UnionTypes\UnionTypeWithStringBackedEnumTypeAlias;
use Filecage\GraphQL\FactoryTests\Util\FactoryProvider;
use Filecage\GraphQL\FactoryTests\Util\MatchesGQLSnapshot;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\Drivers\JsonDriver;

class UnionTypeTest extends TestCase {
    use FactoryProvider, MatchesGQLSnapshot;

    function testExpectsUnionSubtypeToBeResolved () {
        $schema = new Schema(['query' => $this->provideFactory()->forQuery(GetPersonOrPet::class)]);
        $result = GraphQL::executeQuery($schema, '{ GetPersonOrPet { personOrPet { __typename ... on Person { name } ... on Pet { name } } } }');

        $this->assertMatchesGraphQLSchemaSnapshot($schema);
        $this->assertMatchesSnapshot($result, new JsonDriver());
    }

    function testExpectsUnionSubtypeFromContainerToBeResolved () {
        $schema = new Schema(['query' => $this->provideFactory()->forQuery(GetPersonOrPetContainer::class)]);
        $result = GraphQL::executeQuery($schema, '{ GetPersonOrPetContainer { __typename ... on Person { name } ... on Pet { name } } }');

        $this->assertMatchesGraphQLSchemaSnapshot($schema);
        $this->assertMatchesSnapshot($result, new JsonDriver());
    }

    function testExpectsSimilarUnionSubtypesToBeResolved () {
        $schema = new Schema(['query' => $this->provideFactory()->forQuery(GetPersonOrPet::class, GetFamilyMembers::class)]);
        $result = GraphQL::executeQuery($schema, '{GetPersonOrPet { personOrPet { __typename ... on Person { name } ... on Pet { name } } } GetFamilyMembers  { personOrPet { __typename ... on Person { name } ... on Pet { name } } } }');

        $this->assertMatchesGraphQLSchemaSnapshot($schema);
        $this->assertMatchesSnapshot($result, new JsonDriver());
    }

    function testExpectsUnionSubtypeFromListToBeCreated () {
        $this->assertMatchesTypeSnapshot($this->provideFactory()->forType(ListUnionType::class));
    }

    function testExpectsTypeWithSameAliasButOneNullableAndOneNonNullable () {
        $this->assertMatchesTypeSnapshot($this->provideFactory()->forType(UnionTypeWithSameSignatureButNullableAndNonNullable::class));
    }

    function testExpectsTypeWithStringBackedEnum () {
        $this->assertMatchesTypeSnapshot($this->provideFactory()->forType(UnionTypeWithStringBackedEnumTypeAlias::class));
    }

    function testExpectsExceptionForListUnionPropertyIfContainsIsMissing () {
        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Missing union type `TypeAlias` attribute declaration for property `Filecage\GraphQL\FactoryTests\Fixtures\Types\UnionTypes\ListUnionTypePropertyWithMissingContains::$personOrPet`');

        $this->provideFactory()->forType(ListUnionTypePropertyWithMissingContains::class);
    }

    function testExpectsExceptionForListUnionReturnIfContainsIsMissing () {
        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Missing union type `TypeAlias` attribute declaration for return type of `Filecage\GraphQL\FactoryTests\Fixtures\Types\UnionTypes\ListUnionTypeReturnWithMissingContains::getPersonOrPet()`');

        $this->provideFactory()->forType(ListUnionTypeReturnWithMissingContains::class);
    }

    function testExpectsExceptionForListUnionContainerIfContainsIsMissing () {
        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Missing union type `TypeAlias` attribute declaration for class `Filecage\GraphQL\FactoryTests\Fixtures\Types\UnionTypes\ListUnionTypeContainerWithMissingContains`');

        $this->provideFactory()->forType(ListUnionTypeContainerWithMissingContains::class);
    }

    function testExpectsExceptionIfTypeAliasIsMissing () {
        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Missing union type `TypeAlias` attribute declaration for property `Filecage\GraphQL\FactoryTests\Fixtures\Types\UnionTypes\UnionTypeWithMissingTypeAlias::$personOrPet`');

        $this->provideFactory()->forType(UnionTypeWithMissingTypeAlias::class);
    }

    function testExpectsExceptionIfTypeAliasIsUsedMultipleTimesWithDifferentSignature () {
        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Unsupported union type: A previously defined type alias `PersonOrPet` is different to the one of');
        $this->provideFactory()->forType(UnionTypeWithMultipleTypeAliasAndDifferentSignature::class);
    }

}