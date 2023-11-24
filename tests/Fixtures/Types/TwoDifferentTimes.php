<?php

namespace Filecage\GraphQL\FactoryTests\Fixtures\Types;

final class TwoDifferentTimes {
    function __construct (
        public ?\DateTime $one,
        public ?\DateTimeImmutable $two,
    ) {}
}