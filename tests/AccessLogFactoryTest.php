<?php

namespace Test;

use App\Entities\Factories\AccessLogFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class AccessLogFactoryTest extends TestCase
{
    private AccessLogFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new AccessLogFactory();
    }

    public function logsDataProvide(): array
    {
        return [
            'logs' => [
                '224.43.6.50 - - [29/09/2023:01:51:46 +1000] "OPTION http://ledner.biz/non-totam-quibusdam-et-et-perspiciatis-ipsam.html HTTP/1.1" 200 2 3.000000 "-" "@list-item-updater" prio:0',
                '152.147.70.163 - - [29/09/2023:01:51:47 +1000] "HEAD http://www.brakus.info/molestiae-eveniet-veritatis-ut-ducimus HTTP/1.1" 200 2 3.000000 "-" "@list-item-updater" prio:0',
                '237.198.75.255 - - [29/09/2023:01:51:47 +1000] "DELETE http://hessel.com/corrupti-provident-officiis-rerum-et-consequuntur HTTP/1.1" 200 2 8.000000 "-" "@list-item-updater" prio:0',
                '216.149.249.98 - - [29/09/2023:01:51:47 +1000] "OPTION https://www.oberbrunner.org/culpa-labore-qui-error-suscipit-dolorem-minima-omnis-architecto HTTP/1.1" 200 2 41.000000 "-" "@list-item-updater" prio:0',
                '132.67.225.70 - - [29/09/2023:01:51:47 +1000] "POST http://ward.org/ HTTP/1.1" 200 2 42.000000 "-" "@list-item-updater" prio:0',
                '18.3.106.244 - - [29/09/2023:01:51:47 +1000] "POST http://www.beatty.com/ HTTP/1.1" 500 2 37.000000 "-" "@list-item-updater" prio:0',
                '220.144.192.60 - - [29/09/2023:01:51:48 +1000] "DELETE http://schaefer.com/magnam-sunt-autem-accusamus-ex-voluptatum-ex-qui.html HTTP/1.1" 200 2 32.000000 "-" "@list-item-updater" prio:0',
                '188.106.58.156 - - [29/09/2023:01:51:48 +1000] "PUT https://hudson.org/ea-odit-natus-omnis-expedita-voluptate-id.html HTTP/1.1" 200 2 20.000000 "-" "@list-item-updater" prio:0',
                '45.115.226.99 - - [29/09/2023:01:51:48 +1000] "GET http://herman.com/et-neque-necessitatibus-quo-modi-beatae HTTP/1.1" 200 2 29.000000 "-" "@list-item-updater" prio:0',
                '189.69.160.139 - - [29/09/2023:01:51:49 +1000] "GET https://www.swaniawski.com/autem-ut-est-odit-quam HTTP/1.1" 200 2 19.000000 "-" "@list-item-updater" prio:0',
            ],
        ];
    }

    /** @dataProvider logsDataProvide */
    public function testCreate(string $log): void
    {
        $log = $this->factory->createFromString($log);

        $this->assertNotNull($log);
    }
}
