<?php declare(strict_types=1);
namespace app\storage;

/**
 * Interface Storage
 * @package app\storage
 */
interface Storage
{
    public function getConnection();
}