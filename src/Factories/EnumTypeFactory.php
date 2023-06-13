<?php

namespace Filecage\GraphQL\Factory\Factories;

use GraphQL\Type\Definition\EnumType;

final class EnumTypeFactory implements TypeFactory {

    function __construct (private readonly \ReflectionEnum $reflectionEnum) {}

    function create (): EnumType {
        return new EnumType([
            'name' => $this->reflectionEnum->getShortName(),
            'values' => [...$this->generateValues()]
        ]);
    }

    private function generateValues () : \Generator {
        foreach ($this->reflectionEnum->getCases() as $case) {
            $value = ['name' => $case->name];

            if ($case instanceof \ReflectionEnumBackedCase) {
                $value['value'] = $case->getBackingValue();
            }

            yield $case->name => $value;
        }
    }
}