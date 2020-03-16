<?php

namespace App\Console\Commands\Base;

use App\Console\App;

abstract class AbstractCommand
{
    protected $stdin = STDIN;
    protected $stdout = STDOUT;
    protected $stderr = STDERR;

    /**
     * Print line
     * @param string $line
     * @param string|null $fg
     */
    protected function line(string $line, string $fg = null): void
    {
        fwrite($this->stdout, "\033[" . $fg . "m" . $line . "\033[0m\n");
    }

    /**
     * Print line
     * @param string $line
     * @param string $fg
     */
    protected function error(string $line, string $fg = App::COLOR_RED): void
    {
        fwrite($this->stderr, "\033[" . $fg . "m" . $line . "\033[0m\n");
    }

    /**
     * Print line
     * @param string $str
     * @param string|null $fg
     */
    protected function color(string $str, string $fg = null): void
    {
        fwrite($this->stdout, "\033[" . $fg . "m" . $str . "\033[0m");
    }
}
