<?php

namespace App\Repositories;

interface IAccessLogRepository
{
    public function getChunksGenerator(int $limit = 5);
}
