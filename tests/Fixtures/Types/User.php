<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Types;

class User {
    function __construct (
        public readonly int $id,
        public readonly Person $person,
        public readonly UserType $type,
    ) {}
}