<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Types;

use Filecage\GraphQL\Annotations\Attributes\Contains;
use Filecage\GraphQL\FactoryTests\Fixtures\Containers\PetsContainer;

final class PetOwner {
    function __construct (
        public readonly Person $person,
        public readonly PetsContainer $pets) {}
}