<?php

namespace Filecage\GraphQL\Factory\Interfaces\Types;

use Filecage\GraphQL\Factory\Exceptions\InvalidTypeException;

/**
 * @internal
 */
interface Cacheable {

    /**
     * Allows defining a differing internal cache name when a type is used for multiple different internal types (e.g. for `DateTime`, `DateTimeImmutable`)
     *
     * @throws InvalidTypeException
     * @return string
     */
    function getCacheTypeName () : string;

}