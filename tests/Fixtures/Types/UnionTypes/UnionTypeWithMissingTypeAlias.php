<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Types\UnionTypes;

use Filecage\GraphQL\FactoryTests\Fixtures\Types\Person;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Pet;

final class UnionTypeWithMissingTypeAlias {
    readonly Person|Pet $personOrPet;
}