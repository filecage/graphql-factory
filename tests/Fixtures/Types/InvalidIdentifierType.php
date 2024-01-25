<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Types;

use Filecage\GraphQL\Annotations\Attributes\Identifier;

final class InvalidIdentifierType {

    function __construct (
        #[Identifier]
        public \stdClass $identifier
    ) {}

}