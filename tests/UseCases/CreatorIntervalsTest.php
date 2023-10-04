<?php

namespace Tests\UseCases;

use App\UseCases\CreatorIntervals;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class CreatorIntervalsTest extends TestCase
{
    private Carbon $startedAt;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->startedAt = Carbon::now()->days(-1);
    }

    public function dataProviderPoints(): array
    {
        return [
            'simple interval' => [
                [
                    $this->startedAt->unix(),
                    $this->startedAt->addSeconds(1)->unix(),
                ],
                1
            ],
            'many intervals' => [
                [
                    $this->startedAt->unix(),
                    $this->startedAt->addSeconds(1)->unix(),
                    $this->startedAt->addSeconds(2)->unix(),
                    $this->startedAt->addSeconds(1)->unix(),
                    $this->startedAt->addSeconds(5)->unix(),
                    $this->startedAt->addSeconds(1)->unix(),
                ],
                3
            ],
            'no intervals' => [
                [],
                0
            ],
            'many intervals as points' => [
                [
                    $this->startedAt->unix(),
                    $this->startedAt->addSeconds(2)->unix(),
                    $this->startedAt->addSeconds(3)->unix(),
                    $this->startedAt->addSeconds(4)->unix(),
                    $this->startedAt->addSeconds(5)->unix(),
                    $this->startedAt->addSeconds(6)->unix(),
                ],
                6
            ],
        ];
    }

    /** @dataProvider dataProviderPoints */
    public function testCreate(array $points, int $expectIntervalCount): void
    {
        $creator = new CreatorIntervals();
        $intervals = $creator->byPoints($points);
        $this->assertCount($expectIntervalCount, $intervals);
    }
}
