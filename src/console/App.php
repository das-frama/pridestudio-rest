<?php

declare(strict_types=1);

namespace app\console;

use Dice\Dice;
use RecursiveDirectoryIterator;

class App
{
    const COMMAND_PATH = 'app\console\command';
    const COLOR_DARK_GREY = '1;30';
    const COLOR_LIGHT_GREY = '0;37';
    const COLOR_GREEN = '0;32';
    const COLOR_YELLOW = '1;33';

    /** @var Dice */
    private $dice;

    public function __construct(array $config)
    {
        // DI.
        $this->dice = (new Dice())->addRules($config['rules']);
    }

    public function run(array $argv): int
    {
        $cmd = $this->parseCmd($argv);
        if ($cmd === null) {
            $this->printHelp();
            return 0;
        }
        $class = sprintf('%s\%sCommand', static::COMMAND_PATH, ucfirst($cmd));
        if (!class_exists($class)) {
            $this->printHelp();
            return 0;
        }
        $command = $this->dice->create($class);
        return call_user_func([$command, 'main'], ...array_slice($argv, 2));
    }

    private function parseCmd(array $argv): ?string
    {
        if (count($argv) <= 1) {
            return null;
        }
        return strtolower($argv[1]);
    }

    private function printHelp(): void
    {
        fwrite(STDOUT, "Pridestudio app 0.1.0\n\n");
        fwrite(STDOUT, $this->getColoredString("Usage:\n", static::COLOR_YELLOW));
        fwrite(STDOUT, "  command [options] [arguments]\n");
        fwrite(STDOUT, $this->getColoredString("Available commands:\n", static::COLOR_YELLOW));
        $commands = $this->availableCommands();
        foreach ($commands as $command) {
            $cmdStr = $this->getColoredString($command, static::COLOR_GREEN);
            fwrite(STDOUT, sprintf("  %s\t%s\n", $cmdStr, "Description"));
        }
    }

    /**
     * Get all available commands.
     * @return string[]
     */
    private function availableCommands(): array
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'command';
        $files = new RecursiveDirectoryIterator($path);
        $commands = [];
        foreach ($files as $file) {
            $name = $file->getFilename();
            if ($name === '.' || $name === '..') {
                continue;
            }
            $commands[] = strtolower(strstr($name, 'Command.php', true));
        }
        return $commands;
    }

    private function getColoredString(string $str, string $fg): string
    {
        return "\033[" . $fg . "m" . $str . "\033[0m";
    }
}
