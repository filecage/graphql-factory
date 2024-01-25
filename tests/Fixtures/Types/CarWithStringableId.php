<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Types;

use Filecage\GraphQL\Annotations\Attributes\Identifier;

final class CarWithStringableId {

    function __construct (
        #[Identifier]
        public CarIdentifier $id,
        public string $name,
    ) {}

}