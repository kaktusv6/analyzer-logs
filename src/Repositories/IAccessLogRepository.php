<?php

namespace App\Repositories;

/** Репозиторий по работе с access логами */
interface IAccessLogRepository
{
    /**
     * Метод потокового возврата чанков с логами
     *
     * @param int $limit Размер чанка
     */
    public function getChunksGenerator(int $limit = 5): \Generator;
}
