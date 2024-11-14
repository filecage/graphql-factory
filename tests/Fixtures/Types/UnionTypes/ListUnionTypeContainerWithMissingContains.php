<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Types\UnionTypes;


use Filecage\GraphQL\Annotations\Attributes\Contains;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Person;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Pet;

#[Contains(Person::class)]
#[Contains(Pet::class)]
final class ListUnionTypeContainerWithMissingContains implements \Iterator {

    public function current (): mixed {
        // TODO: Implement current() method.
    }

    public function next (): void {
        // TODO: Implement next() method.
    }

    public function key (): mixed {
        // TODO: Implement key() method.
    }

    public function valid (): bool {
        // TODO: Implement valid() method.
    }

    public function rewind (): void {
        // TODO: Implement rewind() method.
    }
}