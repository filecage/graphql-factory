<?php

use Filecage\GraphQL\Factory\Factory;
use GraphQL\Utils\SchemaPrinter;

require_once __DIR__ . '/../vendor/autoload.php';


enum MyEnumValues : string {
    case FOO = 'foo';
}


$factory = new Factory(fn() => null);
$type = $factory->forType(MyEnumValues::class);

echo SchemaPrinter::printType($type);