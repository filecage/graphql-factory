<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Types;

use Filecage\GraphQL\Annotations\Attributes\Identifier;

final class Car {

    function __construct (
        #[Identifier]
        public string $id,
        public string $name,
    ) {}

}