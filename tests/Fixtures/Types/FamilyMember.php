<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Types;

use Filecage\GraphQL\Annotations\Attributes\TypeAlias;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\UnionTypes\UnionTypeEnum;
use GraphQL\Type\Definition\Description;

/**
 * This is a deliberate duplication of a second class with the same contents to ensure
 * a similar union type doesn't clash in the GraphQL schema
 * @see PersonOrPet
 */
final class FamilyMember {
    function __construct(
        #[Description("A Person Or Pet, why would we make a difference here?")]
        #[TypeAlias(UnionTypeEnum::PersonOrPetUnion)]
        public readonly Person|Pet $personOrPet
    ) {}
}