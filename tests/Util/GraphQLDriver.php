<?php

namespace Filecage\GraphQL\FactoryTests\Util;

use GraphQL\Error\Error;
use GraphQL\Error\SerializationError;
use GraphQL\Type\Definition\Type;
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
        if ($data instanceof Schema) {
            return SchemaPrinter::doPrint($data);
        }

        if ($data instanceof Type) {
            return SchemaPrinter::printType($data);
        }

        throw new CantBeSerialized('Given data is not a GraphQL schema or type');
    }

    function extension (): string {
        return 'gql';
    }

    /**
     * @param mixed $expected
     * @param Schema|Type|mixed $actual
     *
     * @return void
     * @throws CantBeSerialized
     * @throws Error
     * @throws SerializationError
     * @throws \JsonException
     */
    function match (mixed $expected, mixed $actual): void {
        if (!$actual instanceof Schema && !$actual instanceof Type) {
            throw new CantBeSerialized('Given data is not a GraphQL schema or type');
        }

        if ($this->assertValid) {
            $actual->assertValid();
        }

        Assert::assertSame($expected, $actual instanceof Type ? SchemaPrinter::printType($actual) : SchemaPrinter::doPrint($actual));
    }
}