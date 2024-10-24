<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Types\UnionTypes;

use Filecage\GraphQL\Annotations\Attributes\TypeAlias;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Person;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Pet;

final class UnionTypeWithSameSignatureButNullableAndNonNullable {

    #[TypeAlias('PersonOrPet')]
    readonly Person|Pet $personOrPet;

    #[TypeAlias('PersonOrPet')]
    readonly null|Person|Pet $personOrPetOrNull;
}