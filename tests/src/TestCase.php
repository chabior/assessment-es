<?php

namespace App\Tests;

use App\ES\AggregateRoot;
use App\ES\AggregateRootTranslator;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function popRecordedEvents(AggregateRoot $aggregateRoot):array
    {
        return (new AggregateRootTranslator())->popRecordedEvents($aggregateRoot);
    }
}