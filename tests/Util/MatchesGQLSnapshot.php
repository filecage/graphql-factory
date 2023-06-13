<?php

namespace Filecage\GraphQL\FactoryTests\Util;

use GraphQL\Type\Schema;
use Spatie\Snapshots\MatchesSnapshots;

trait MatchesGQLSnapshot {
    use MatchesSnapshots;

    function assertMatchesGraphQLSchemaSnapshot (Schema $schema, bool $assertValid = true) : void {
        $this->assertMatchesSnapshot($schema, new GraphQLDriver($assertValid));
    }

}