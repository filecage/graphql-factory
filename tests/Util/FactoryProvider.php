<?php

namespace Filecage\GraphQL\FactoryTests\Util;

use Filecage\GraphQL\Factory\Factory;

trait FactoryProvider {

    function provideFactory () : Factory {
        return new Factory(fn() => null);
    }

}