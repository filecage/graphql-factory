<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Types\UnionTypes;

enum UnionTypeStringBackedEnum : string {
    case PersonOrPetUnion = 'StringBackedPersonOrPetUnionType';
}