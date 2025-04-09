<?php

namespace Filecage\GraphQL\FactoryTests;

use Filecage\GraphQL\FactoryTests\Fixtures\Mutations\SetUser;
use Filecage\GraphQL\FactoryTests\Fixtures\Mutations\UpdateUser;
use Filecage\GraphQL\FactoryTests\Fixtures\Queries\GetUser;
use Filecage\GraphQL\FactoryTests\Util\FactoryProvider;
use Filecage\GraphQL\FactoryTests\Util\MatchesGQLSnapshot;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;

class InputTypeTest extends TestCase {
    use FactoryProvider, MatchesGQLSnapshot;

    function testExpectsQueryWithWithTypePromiseToBeResolved () {
        $factory = $this->provideFactory();

        /** @var ObjectType $mutation */
        $mutation = $factory->forQuery(SetUser::class, UpdateUser::class);
        $mutation->name = 'Mutation';

        $schema = new Schema(['query' => $factory->forQuery(GetUser::class), 'mutation' => $mutation]);
        $result = GraphQL::executeQuery($schema, 'mutation {SetUser (id: 1, user: { name: "Lando" type: NormalUser, additionally: { alsoUserType: Admin } } ) { person { name, nameHashed }, type } }');

        $this->assertMatchesQuerySnapshot($result);
    }
}