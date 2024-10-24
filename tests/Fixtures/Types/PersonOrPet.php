<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Types;

use Filecage\GraphQL\Annotations\Attributes\TypeAlias;
use GraphQL\Type\Definition\Description;

/**
 * This is a deliberate duplication of a second class with the same contents to ensure
 * a similar union type doesn't clash in the GraphQL schema
 * @see FamilyMember
 */

final class PersonOrPet {
    function __construct(
        #[Description("A Person Or A Pet, it could be both!")]
        #[TypeAlias("PersonOrPetUnion")]
        public readonly Person|Pet $personOrPet
    ) {}
}