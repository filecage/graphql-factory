<?php

namespace Filecage\GraphQL\Factory\Types;

use DateTimeInterface;
use Filecage\GraphQL\Factory\Interfaces\Types\Cacheable;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class DateTimeType extends ObjectType implements Cacheable {
    function __construct () {
        parent::__construct([
            'name' => 'DateTime',
            'description' => 'DateTime in ISO8601 representation (YYYY-MM-DDTHH:MM:SSZ)',
            'fields' => [
                'iso8601' => [
                    'type' => Type::string(),
                    'description' => 'DateTime as ISO8601 string',
                    'resolve' => fn(DateTimeInterface $dateTime) => $dateTime->format('c'),
                ],
                'ymd' => [
                    'type' => Type::string(),
                    'description' => 'Date only in the format of YYYY-MM-DD (e.g. 2023-08-22), all leading zeros',
                    'resolve' => fn(DateTimeInterface $dateTime) => $dateTime->format('Y-m-d'),
                ],
                'hms' => [
                    'type' => Type::string(),
                    'description' => 'Time only in the format of H:M:S (e.g. 21:42:04)',
                    'resolve' => fn(DateTimeInterface $dateTime) => $dateTime->format('H:i:s'),
                ],
                'tz' => [
                    'type' => Type::string(),
                    'description' => 'Timezone only as identifier representation',
                    'resolve' => fn(DateTimeInterface $dateTime) => $dateTime->format('e')
                ]
            ],
        ]);
    }

    function getCacheTypeName (): string {
        return DateTimeInterface::class;
    }
}