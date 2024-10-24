<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Types\UnionTypes;

use Filecage\GraphQL\Annotations\Attributes\TypeAlias;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Person;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Pet;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\User;

final class UnionTypeWithMultipleTypeAliasAndDifferentSignature {

    #[TypeAlias('PersonOrPet')]
    readonly Person|Pet $personOrPet;

    #[TypeAlias('PersonOrPet')]
    readonly User|Person|Pet $userOrPersonOrPet;
}