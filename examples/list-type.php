<?php

use Filecage\GraphQL\Factory\Attributes\Contains;
use Filecage\GraphQL\Factory\Enums\ScalarType;
use Filecage\GraphQL\Factory\Factory;
use Filecage\GraphQL\Factory\Queries\Query;
use Filecage\GraphQL\Factory\TypeTransformer\IterableTypeTransformer;
use GraphQL\Type\Schema;
use GraphQL\Utils\SchemaPrinter;

require_once __DIR__ . '/../vendor/autoload.php';

class ItemValue {
    public ?string $value;
}

class User {

    #[Contains(ItemValue::class, true)]
    public array $items = [];

    /**
     * @param string $name
     * @param array $awards
     */
    function __construct (public readonly string $name, #[Contains(ScalarType::STRING)] public readonly ?array $awards) {}
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
        return [new User('Hello', ['First', 'Second']), new User('World', null)];
    }
}

$factory = new Factory(fn() => null);
$schema = new Schema(['query' => $factory->forQuery(MyQuery::class)]);
$schema->assertValid();
echo SchemaPrinter::doPrint($schema);