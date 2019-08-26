<?php

declare(strict_types=1);

namespace app\domain\record;

use app\entity\Record;
use app\storage\mongodb\RecordRepository;

class RecordService
{
    /**
     * @var RecordRepositoryInterface
     */
    private $recordRepo;

    public function __construct(RecordRepository $recordRepo)
    {
        $this->recordRepo = $recordRepo;
    }

    public function findByID(string $id): ?Record
    {
        return $this->recordRepo->findByID($id);
    }

    /**
     * @return Record[]
     */
    public function findAll(int $limit, int $offset): array
    {
        return $this->recordRepo->findAll($limit, $offset);
    }
}
