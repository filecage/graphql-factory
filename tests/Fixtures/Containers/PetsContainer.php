<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Containers;

use Filecage\GraphQL\Annotations\Attributes\Contains;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Pet;

#[Contains(Pet::class)]
class PetsContainer extends \ArrayIterator {

    function __construct(Pet ...$peopleOrPets) {
        parent::__construct($peopleOrPets);
    }

}