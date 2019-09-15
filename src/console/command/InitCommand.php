<?php

declare(strict_types=1);

namespace app\console\command;

class InitCommand
{
    public function main(): int
    {
        fwrite(STDOUT, "hello from init!\n");
        return 0;
    }
}
