<?php

use Filecage\GraphQLFactory\Factory;
use Filecage\GraphQLFactory\Queries\Query;
use Filecage\GraphQLFactory\TypeTransformer\IterableTypeTransformer;
use GraphQL\Type\Schema;
use GraphQL\Utils\SchemaPrinter;

require_once __DIR__ . '/../vendor/autoload.php';

class User {
    function __construct (public readonly string $name) {}
}

class MyQuery extends Query {
    function __construct () {
        parent::__construct(
            "Example for how to use iterable types",
            User::class,
            new IterableTypeTransformer()
        );
    }

    function resolve (mixed $rootValue = null, array $arguments = []): null|object|iterable|callable {
        return [new User('Hello'), new User('World')];
    }
}

$factory = new Factory(fn() => null);
$schema = new Schema(['query' => $factory->forQuery(MyQuery::class)]);
$schema->assertValid();
echo SchemaPrinter::doPrint($schema);