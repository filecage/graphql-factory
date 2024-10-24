<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Queries;

use Filecage\GraphQL\Factory\Queries\Query;
use Filecage\GraphQL\Factory\TypeTransformer\IterableTypeTransformer;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Person;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\PersonOrPet;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Pet;

class GetPersonOrPet extends Query {

    function __construct() {
        parent::__construct(
            description: 'Generates a list of mixed outputs to test union sub-types',
            returnTypeClassName: PersonOrPet::class,
            transformer: new IterableTypeTransformer(),
        );
    }

    function resolve (mixed $rootValue = null, array $arguments = []): array {
        return [
            new PersonOrPet(new Person('David')),
            new PersonOrPet(new Pet('Nox')),
        ];
    }
}