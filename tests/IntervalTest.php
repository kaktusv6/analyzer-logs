<?php

namespace Tests;

use App\ValueObjects\Interval;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class IntervalTest extends TestCase
{
    public function testInit(): void
    {
        $interval = new Interval(0, 1);
        $this->assertNotNull($interval);
    }

    public function testInitToOnePoint(): void
    {
        $interval = new Interval(1, 1);
        $this->assertIsBool($interval->isToOnePoint());
    }

    public function testAssert(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $interval = new Interval(1, 0);
    }
}
