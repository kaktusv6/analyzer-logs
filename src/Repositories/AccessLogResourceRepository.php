<?php

namespace App\Repositories;

use App\Entities\Factories\AccessLogFactory;
use App\Utils\Files\Reader;

final class AccessLogResourceRepository implements IAccessLogRepository
{
    private Reader $reader;

    private AccessLogFactory $factory;

    public function __construct(Reader $reader, AccessLogFactory $factory)
    {
        $this->reader = $reader;

        $this->factory = $factory;
    }

    public function getChunksGenerator(int $limit = 5)
    {
        while (!$this->reader->feof()) {
            $result = [];
            $logsStr = $this->reader->readLines($limit);
            foreach ($logsStr as $logStr) {
                $result[] = $this->factory->createFromString($logStr);
            }

            yield $result;
        }
    }
}
