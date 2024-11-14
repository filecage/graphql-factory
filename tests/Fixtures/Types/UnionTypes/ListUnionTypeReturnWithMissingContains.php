<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Types\UnionTypes;


use Filecage\GraphQL\Annotations\Attributes\Contains;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Person;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Pet;

final class ListUnionTypeReturnWithMissingContains {
    #[Contains(Person::class)]
    #[Contains(Pet::class)]
    function getPersonOrPet() : array {
        return [];
    }
}