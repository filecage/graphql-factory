<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Queries;

use Filecage\GraphQL\Factory\Queries\Query;
use Filecage\GraphQL\Factory\TypeTransformer\IterableTypeTransformer;
use Filecage\GraphQL\FactoryTests\Fixtures\Containers\PetsContainer;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\FamilyMember;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Person;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\PersonOrPet;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Pet;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\PetOwner;

class GetFamilyMembers extends Query {

    function __construct() {
        parent::__construct(
            description: 'Generates a list of mixed outputs to test union sub-types',
            returnTypeClassName: FamilyMember::class,
            transformer: new IterableTypeTransformer(),
        );
    }

    function resolve(mixed $rootValue = null, array $arguments = []) : \Generator {
        yield new FamilyMember(new Person('David'));
        yield new FamilyMember(new Pet('Nox'));
    }
}