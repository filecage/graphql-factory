<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Queries;

use Filecage\GraphQL\Factory\Queries\Query;
use Filecage\GraphQL\Factory\TypeTransformer\IterableTypeTransformer;
use Filecage\GraphQL\FactoryTests\Fixtures\Containers\PetsContainer;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Person;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Pet;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\PetOwner;

class GetPetOwners extends Query {

    function __construct() {
        parent::__construct(
            description: 'Generates a list with wrapped sub-lists using an ArrayIterator instance',
            returnTypeClassName: PetOwner::class,
            transformer: new IterableTypeTransformer(),
        );
    }

    function resolve(mixed $rootValue = null, array $arguments = []) : \Generator {
        yield new PetOwner(new Person('David'), new PetsContainer(new Pet('Lenny'), new Pet('Nox')));
        yield new PetOwner(new Person('Julia'), new PetsContainer(new Pet('Nox')));
    }
}