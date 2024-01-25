<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Types;

use Filecage\GraphQL\Annotations\Attributes\Identifier;

final class CarWithScalarId {

    function __construct (
        #[Identifier]
        public string $id,
        public string $name,
    ) {}

}