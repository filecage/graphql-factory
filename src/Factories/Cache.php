<?php

namespace Filecage\GraphQL\Factory\Factories;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;

/**
 * @internal
 */
final class Cache {

    /**
     * @var array<class-string, Type>
     */
    private array $types = [];

    /** @var array<string, array{type: UnionType, signature: string}> */
    private array $unions = [];

    /**
     * @param class-string $key
     *
     * @return Type|null
     */
    function get (string $key) : ?Type {
        return $this->types[$key] ?? null;
    }

    function has (string $key) : bool {
        return array_key_exists($key, $this->types);
    }

    function set (string $key, Type $type) : void {
        $this->types[$key] = $type;
    }

    function hasUnion (string $key) : bool {
        return array_key_exists($key, $this->unions);
    }

    function getUnionType (string $key) : ?UnionType {
        return $this->unions[$key]['type'] ?? null;
    }

    function getUnionSignature (string $key) : ?string {
        return $this->unions[$key]['signature'] ?? null;
    }

    function setUnion (string $key, UnionType $type, string $signature) : void {
        $this->unions[$key] = [
            'signature' => $signature,
            'type' => $type,
        ];
    }

}