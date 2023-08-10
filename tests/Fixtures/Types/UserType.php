<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Types;

use GraphQL\Type\Definition\Description;

enum UserType {
    #[Description('A normal user without any special rights')]
    case NormalUser;

    #[Description('An admin user with the right to rule the world')]
    case Admin;
}