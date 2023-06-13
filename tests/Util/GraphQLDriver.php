<?php

namespace Filecage\GraphQL\FactoryTests\Util;

use GraphQL\Error\Error;
use GraphQL\Error\SerializationError;
use GraphQL\Type\Schema;
use GraphQL\Utils\SchemaPrinter;
use PHPUnit\Framework\Assert;
use Spatie\Snapshots\Driver;
use Spatie\Snapshots\Exceptions\CantBeSerialized;

final class GraphQLDriver implements Driver {

    function __construct (private readonly bool $assertValid = true) {}

    /**
     * @param Schema $data
     *
     * @return string
     * @throws CantBeSerialized
     * @throws Error
     * @throws SerializationError
     * @throws \JsonException
     */
    function serialize (mixed $data): string {
        if (!$data instanceof Schema) {
            throw new CantBeSerialized('Given data is not a GraphQL schema or type');
        }

        return SchemaPrinter::doPrint($data);
    }

    function extension (): string {
        return 'gql';
    }

    function match (mixed $expected, mixed $actual) {
        if (!$actual instanceof Schema) {
            throw new CantBeSerialized('Given data is not a GraphQL schema or type');
        }

        if ($this->assertValid) {
            $actual->assertValid();
        }

        Assert::assertSame($expected, SchemaPrinter::doPrint($actual));
    }
}