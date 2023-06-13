<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Types;

class Person {
    function __construct (
        public readonly string $name
    ) {}
}