<?php

use Filecage\GraphQL\Annotations\Attributes\Ignore;
use Filecage\GraphQL\Factory\Factory;
use GraphQL\Utils\SchemaPrinter;

require_once __DIR__ . '/../vendor/autoload.php';


class User {

    #[Ignore]
    public array $items = [];

    function __construct (public readonly string $name, #[Ignore] public readonly ?array $awards) {}

    #[Ignore]
    function getFoobar () : array {
        return [];
    }
}


$factory = new Factory(fn() => null);
$type = $factory->forType(User::class);

echo SchemaPrinter::printType($type);