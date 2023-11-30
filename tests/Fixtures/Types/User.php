<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Types;

use Filecage\GraphQL\Annotations\Attributes\Ignore;
use GraphQL\Type\Definition\Description;

class User {
    function __construct (
        #[Description('The user ID of this entity')]
        public readonly int $id,
        public readonly Person $person,
        public readonly UserType $type,

        #[Ignore]
        public readonly string $passwordAlgo = '',
    ) {}
}