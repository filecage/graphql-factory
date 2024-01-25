<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Types;

use Filecage\GraphQL\Annotations\Attributes\Identifier;/**
 * This class simulates a situation where you have an object/class that you want to
 * have in your PHP code, but the public GQL API really only needs a single property
 * We use the `Reduce` attribute then to skip the full structure of that class
 */
final class CarIdentifier implements \Stringable {

    function __construct (
        public int $carIdentifier
    ) {}

    public function __toString (): string {
        return $this->carIdentifier;
    }
}