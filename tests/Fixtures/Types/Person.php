<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Types;

use GraphQL\Type\Definition\Description;

class Person {
    function __construct (
        public readonly string $name
    ) {}

    #[Description("SHA256 hash of the Persons's name")]
    function getNameHashed () : string {
        return hash('sha256', $this->name);
    }

}