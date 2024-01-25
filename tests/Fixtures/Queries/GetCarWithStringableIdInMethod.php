<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Queries;

use Filecage\GraphQL\Factory\Queries\Argument;
use Filecage\GraphQL\Factory\Queries\Query;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\CarIdentifier;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\CarWithScalarId;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\CarWithStringableId;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\CarWithStringableIdInMethod;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Person;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\User;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\UserType;
use GraphQL\Type\Definition\Type;

class GetCarWithStringableIdInMethod extends Query {

    private const CARS = [
        1 => ['id' => 1, 'name' => 'Volkswagen Scirocco'],
        2 => ['id' => 2, 'name' => 'Mercedes C43'],
    ];

    function __construct() {
        parent::__construct(
            description: 'Loads cars and their identifiers for reduced object type testing',
            returnTypeClassName: CarWithStringableIdInMethod::class,
            arguments: new Argument(
                description: "The car's ID", name: 'id', type: Type::nonNull(Type::id())
            )
        );
    }

    function resolve(mixed $rootValue = null, array $arguments = []) : ?CarWithStringableIdInMethod {
        ['id' => $id, 'name' => $name] = self::CARS[$arguments['id']] ?? null;
        if ($name === null) {
            return null;
        }

        return new CarWithStringableIdInMethod(new CarIdentifier($id), $name);
    }
}