<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Containers;

use Filecage\GraphQL\Annotations\Attributes\Contains;
use Filecage\GraphQL\Annotations\Attributes\TypeAlias;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Person;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Pet;

#[Contains(Pet::class)]
#[Contains(Person::class)]
#[TypeAlias('PersonOrPetContainerUnion')]
class PersonOrPetContainer extends \ArrayIterator {

    function __construct(Pet|Person ...$peopleOrPets) {
        parent::__construct($peopleOrPets);
    }

}