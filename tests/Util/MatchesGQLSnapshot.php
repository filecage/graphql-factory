<?php

namespace Filecage\GraphQL\FactoryTests\Util;

use GraphQL\Executor\ExecutionResult;
use GraphQL\Type\Schema;
use Spatie\Snapshots\Drivers\JsonDriver;
use Spatie\Snapshots\MatchesSnapshots;

trait MatchesGQLSnapshot {
    use MatchesSnapshots;

    function assertMatchesGraphQLSchemaSnapshot (Schema $schema, bool $assertValid = true) : void {
        $this->assertMatchesSnapshot($schema, new GraphQLDriver($assertValid));
    }

    function assertMatchesQuerySnapshot (ExecutionResult $executionResult) : void {
        if (!empty($executionResult->errors)) {
            throw new \Exception($executionResult->errors[0]->getMessage());
        }

        $this->assertMatchesSnapshot($executionResult, new JsonDriver());
    }

}