<?php

namespace App\Fakers;

use Carbon\Carbon;
use Faker\Factory;

final class AccessLogFaker
{
    private $factory;

    public function __construct()
    {
        $this->factory = Factory::create();
    }

    public function run(): void
    {
        $startedAt = Carbon::now()
            ->setTimezone(\DateTimeZone::ASIA)
            ->days(-1)
        ;
        $rows = 20000;

        $file = fopen(__DIR__.'/../../resources/logs/access.log', 'w');
        $i = 0;
        while ($i < $rows) {
            $log = [
                $this->factory->ipv4(),
                '- -',
                "[{$startedAt->format('d/m/Y:H:i:s +1000')}]",
                "\"{$this->httpMethod()} {$this->factory->url()} HTTP/1.1\"",
                $this->httpStatus(),
                2,
                "{$this->factory->numberBetween(1, 100)}.000000",
                '"-" "@list-item-updater" prio:0',
                ($i + 1) === $rows ? '' : "\n",
            ];
            $startedAt = $startedAt->addSeconds($this->factory->randomElement([0, 1]));

            fwrite($file, join(' ', $log));
            ++$i;
        }

        fclose($file);
    }

    private function httpMethod(): string
    {
        return $this->factory->randomElement([
            'GET',
            'POST',
            'PUT',
            'DELETE',
            'HEAD',
            'OPTION',
        ]);
    }

    private function httpStatus(): string
    {
        return $this->factory->randomElement([
            200,
            500,
        ]);
    }
}
