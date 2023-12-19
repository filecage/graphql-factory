<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Types;

use Filecage\GraphQL\Annotations\Attributes\Ignore;
use Filecage\GraphQL\Annotations\Attributes\Promote;
use GraphQL\Type\Definition\Description;
use SensitiveParameter;

class Person {
    function __construct (
        public readonly string $name
    ) {}

    #[Description("SHA256 hash of the Persons's name")]
    function getNameHashed () : string {
        return hash('sha256', $this->name);
    }

    #[Ignore]
    function getIgnoredValue () : bool {
        return true;
    }

    #[Promote]
    function isCelebrity () : bool {
        return false;
    }

    function isSecret () : bool {
        return true;
    }

}