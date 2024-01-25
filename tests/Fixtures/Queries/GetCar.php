<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Queries;

use Filecage\GraphQL\Factory\Queries\Argument;
use Filecage\GraphQL\Factory\Queries\Query;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Car;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Person;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\User;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\UserType;
use GraphQL\Type\Definition\Type;

class GetCar extends Query {

    private const CARS = [
        1 => ['id' => 1, 'name' => 'Volkswagen Scirocco'],
        2 => ['id' => 2, 'name' => 'Mercedes C43'],
    ];

    function __construct() {
        parent::__construct(
            description: 'Loads cars and their identifiers for reduced object type testing',
            returnTypeClassName: Car::class,
            arguments: new Argument(
                description: "The car's ID", name: 'id', type: Type::nonNull(Type::id())
            )
        );
    }

    function resolve(mixed $rootValue = null, array $arguments = []) : ?Car {
        ['id' => $id, 'name' => $name] = self::CARS[$arguments['id']] ?? null;
        if ($name === null) {
            return null;
        }

        return new Car($id, $name);
    }
}