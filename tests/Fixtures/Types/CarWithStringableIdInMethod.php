<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Types;

use Filecage\GraphQL\Annotations\Attributes\Identifier;

final class CarWithStringableIdInMethod {

    function __construct (
        private CarIdentifier $id,
        public string $name,
    ) {}

    #[Identifier]
    function getId () : CarIdentifier {
        return $this->id;
    }

}