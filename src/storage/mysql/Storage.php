<?php

declare(strict_types=1);

namespace app\storage\mysql;

use app\storage\StorageInterface;
use PDO;
use PDOException;

class Storage implements StorageInterface
{
    /** @var PDO */
    protected $conn = null;

    /**
     * Storage constructor.
     * @param string $dsn
     * @param string $username
     * @param string $password
     */
    public function __construct(string $dsn, string $username, string $password)
    {
        try {
            $this->conn = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION,
            ]);
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function getConnection(): PDO
    {
        return $this->conn;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findAll(string $table, int $limit, int $offset): array
    {
        try {
            $sth = $this->conn->prepare("SELECT * FROM `{$table}`");
            $sth->execute();

            $result = [];
            while ($data = $sth->fetch(PDO::FETCH_ASSOC)) {
                $result[] = $data;
            }

            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
}
