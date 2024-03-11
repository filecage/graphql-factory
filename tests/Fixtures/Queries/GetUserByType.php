<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Queries;

use Filecage\GraphQL\Factory\Queries\Argument;
use Filecage\GraphQL\Factory\Queries\Query;
use Filecage\GraphQL\Factory\TypeTransformer\IterableTypeTransformer;
use Filecage\GraphQL\FactoryTests\Fixtures\Arguments\UserTypeArgument;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Person;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\User;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\UserType;
use GraphQL\Type\Definition\Type;

class GetUserByType extends Query {

    private const USERS = [
        1 => ['name' => 'David', 'type' => UserType::NormalUser],
        2 => ['name' => 'Also David, but better', 'type' => UserType::Admin],
    ];

    function __construct() {
        parent::__construct(
            description: 'Allows loading users by type',
            returnTypeClassName: User::class,
            transformer: new IterableTypeTransformer(),
            arguments: new UserTypeArgument(),
        );
    }

    function resolve(mixed $rootValue = null, array $arguments = []) : iterable {
        $typeHaystack = UserTypeArgument::pick($arguments);
        foreach (self::USERS as $id => ['name' => $name, 'type' => $type]) {
            if (in_array($type, $typeHaystack)) {
                yield new User($id, new Person($name), $type);
            }
        }
    }
}