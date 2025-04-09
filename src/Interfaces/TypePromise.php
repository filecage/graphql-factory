<?php

namespace Filecage\GraphQL\Factory\Interfaces;

use Filecage\GraphQL\Factory\Factory;
use GraphQL\Type\Definition\Type;

/**
 * This can be used as a type that resolves itself with the use of the current factory instance
 * This is useful in cases where you're defining your own types (especially for InputTypes) and
 * need a reference to another entity in the schema, e.g. an enum or a different type that needs
 * to be the exact same instance throughout the schema
 */
interface TypePromise {
   static function resolveType (Factory $factory) : Type;
}