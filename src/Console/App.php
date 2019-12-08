<?php

declare(strict_types=1);

namespace App\Console;

use Dice\Dice;
use RecursiveDirectoryIterator;

class App
{
    const COMMAND_PATH = 'App\console\command';
    const COLOR_DARK_GREY = '1;30';
    const COLOR_LIGHT_GREY = '0;37';
    const COLOR_GREEN = '0;32';
    const COLOR_YELLOW = '1;33';

    private Dice $dice;

    public function __construct(array $config)
    {
        // DI.
        $this->dice = (new Dice())->addRules($config['rules']);
    }

    public function run(array $argv): int
    {
        $commandName = $this->commandFromArgs($argv);
        if ($commandName === null) {
            $this->printHelp();
            return 0;
        }
        $className = sprintf('%s\%sCommand', static::COMMAND_PATH, $this->toCamelCase($commandName));
        if (!class_exists($className)) {
            $this->printHelp();
            return 0;
        }
        $command = $this->dice->create($className);
        return call_user_func([$command, 'main'], ...array_slice($argv, 2));
    }

    public function printHelp(): void
    {
        fwrite(STDOUT, "Pridestudio App 0.1.0\n\n");
        fwrite(STDOUT, $this->coloredString("Usage:\n", static::COLOR_YELLOW));
        fwrite(STDOUT, "  command [options] [arguments]\n");
        fwrite(STDOUT, $this->coloredString("Available commands:\n", static::COLOR_YELLOW));
        $commands = $this->availableCommands();
        foreach ($commands as $command) {
            $cmdStr = $this->coloredString($command, static::COLOR_GREEN);
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
            $commandName = strstr($name, 'Command.php', true);
            $commands[] = $this->toKebabCase($commandName);
        }
        return $commands;
    }

    private function coloredString(string $str, string $fg): string
    {
        return "\033[" . $fg . "m" . $str . "\033[0m";
    }

    private function toKebabCase(string $string): string
    {
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $string));
    }

    private function toCamelCase(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }

    private function commandFromArgs(array $argv): ?string
    {
        if (count($argv) <= 1) {
            return null;
        }
        return strtolower($argv[1]);
    }
}
