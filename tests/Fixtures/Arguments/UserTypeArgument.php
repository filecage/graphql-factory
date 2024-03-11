<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Arguments;

use Filecage\GraphQL\Factory\Interfaces\Arguments\Resolvable;
use Filecage\GraphQL\Factory\Queries\Argument;
use Filecage\GraphQL\Factory\Queries\ArgumentType;
use Filecage\GraphQL\Factory\TypeTransformer\IterableTypeTransformer;
use Filecage\GraphQL\Factory\TypeTransformer\NonNullTypeTransformer;
use Filecage\GraphQL\Factory\TypeTransformer\TypeTransformerCollection;
use Filecage\GraphQL\FactoryTests\Fixtures\Queries\GetUser;
use Filecage\GraphQL\FactoryTests\Fixtures\Types\UserType;
use GraphQL\Type\Definition\Type;

final class UserTypeArgument extends Argument {

    /**
     * @param array $arguments
     *
     * @return UserType[]
     */
    static function pick (array $arguments) : array {
        $userTypesArgument = $arguments['userType'] ?? null;
        if (!is_array($userTypesArgument)) {
            return [];
        }

        $userTypes = [];
        foreach ($userTypesArgument as $userType){
            if ($userType instanceof UserType) {
                $userTypes[] = $userType;
            }
        }

        return $userTypes;
    }

    function __construct () {
        parent::__construct(
            description: "The user's type", name: 'userType', type: new ArgumentType(UserType::class, new TypeTransformerCollection(new IterableTypeTransformer(true), new NonNullTypeTransformer()))
        );
    }

}