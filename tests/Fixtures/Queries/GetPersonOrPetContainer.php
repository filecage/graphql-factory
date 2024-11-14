<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Queries;

use Filecage\GraphQL\Factory\Queries\Query;
use Filecage\GraphQL\FactoryTests\Fixtures\Containers\PersonOrPetContainer;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Person;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Pet;

class GetPersonOrPetContainer extends Query {

    function __construct() {
        parent::__construct(
            description: 'Generates a list of mixed outputs to test union sub-types containers',
            returnTypeClassName: PersonOrPetContainer::class,
        );
    }

    function resolve (mixed $rootValue = null, array $arguments = []): PersonOrPetContainer {
        return new PersonOrPetContainer(
            new Person('David'),
            new Pet('Nox'),
        );
    }
}