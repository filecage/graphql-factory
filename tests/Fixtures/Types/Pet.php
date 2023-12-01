<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Types;

final class Pet {
    function __construct (public readonly string $name) {}
}