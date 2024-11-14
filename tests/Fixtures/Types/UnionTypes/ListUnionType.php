<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Types\UnionTypes;

use Filecage\GraphQL\Annotations\Attributes\Contains;
use Filecage\GraphQL\Annotations\Attributes\TypeAlias;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Person;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Pet;

final class ListUnionType {

    #[Contains(Person::class)]
    #[Contains(Pet::class)]
    #[TypeAlias('PersonOrPetListUnion')]
    public readonly array $personOrPet;

}