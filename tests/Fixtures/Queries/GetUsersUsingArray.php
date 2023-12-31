<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Queries;

use Filecage\GraphQL\Factory\Queries\Argument;
use Filecage\GraphQL\Factory\Queries\Query;
use Filecage\GraphQL\Factory\TypeTransformer\IterableTypeTransformer;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\Person;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\User;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\UserType;

class GetUsersUsingArray extends Query {

    private const USERS = [
        1 => ['name' => 'David', 'type' => UserType::NormalUser],
        2 => ['name' => 'Also David, but better', 'type' => UserType::Admin],
    ];

    /**
     * @return User[]
     */
    static function getUsersList () : array {
        return array_map(fn(array $user, int $id) => new User($id, new Person($user['name']), $user['type']), self::USERS, array_keys(self::USERS));
    }

    function __construct(bool $allowNullValues = false) {
        parent::__construct(
            description: 'Allows listing all users and uses an array internally',
            returnTypeClassName: User::class,
            transformer: new IterableTypeTransformer($allowNullValues)
        );
    }

    function resolve(mixed $rootValue = null, array $arguments = []) : array {
        return static::getUsersList();
    }
}