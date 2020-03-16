<?php
declare(strict_types=1);

if (!function_exists('config')) {
    /**
     * @param string $path
     * @return string
     */
    function config(string $path): string
    {
        return '';
    }
}

if (!function_exists('random_string')) {
    /**
     * Generate random hex string with length.
     * @param int $len
     * @return string
     */
    function random_string(int $len = 32): string
    {
        return bin2hex(random_bytes($len));
    }
}


