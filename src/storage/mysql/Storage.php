<?php declare(strict_types = 1);
namespace app\storage\mysql;

use PDO;
use PDOException;

class Storage
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

    public function getConnection() : PDO
    {
        return $this->conn;
    }
}
