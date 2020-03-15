<?php
declare(strict_types=1);

namespace App\Console;

use Dice\Dice;

/**
 * Class App
 * @package App\Console
 */
class App
{
    const COMMAND_PATH = 'App\Console\Commands';
    const COLOR_DARK_GREY = '1;30';
    const COLOR_LIGHT_GREY = '0;37';
    const COLOR_GREEN = '0;32';
    const COLOR_YELLOW = '1;33';

    protected array $config;
    protected array $commands;
    protected Dice $dice;

    /**
     * App constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        // DI.
        $this->dice = (new Dice())->addRules($config['rules']);

        // Commands.
        $this->commands = $config['routes']['console'];
    }

    /**
     * @param array $argv
     * @return int
     */
    public function run(array $argv): int
    {
        $cmd = $this->commandFromArgs($argv);
        if ($cmd === null) {
            $this->printHelp();
            return 0;
        }
        $handler = $this->getHandlerFromCommand($cmd);
        if ($handler === null) {
            $this->printHelp();
            return 0;
        }

        // Check params.


        list ($class, $method) = explode('@', $handler);
        $class = sprintf("App\\Console\\Commands\\%s", $class);
        $dice = $this->dice->addRule($class, [
            'call' => [
                [$method, [...array_slice($argv, 2)], Dice::CHAIN_CALL],
            ],
        ]);

        return (int)$dice->create($class);
    }

    /**
     * @param array $argv
     * @return string|null
     */
    private function commandFromArgs(array $argv): ?string
    {
        if (count($argv) <= 1) {
            return null;
        }
        return strtolower($argv[1]);
    }

    /**
     * Print help.
     * @return void
     */
    public function printHelp(): void
    {
        fwrite(STDOUT, sprintf("%s App %s\n\n", $this->config['name'], $this->config['version']));
        fwrite(STDOUT, $this->colored("Usage:\n", static::COLOR_YELLOW));
        fwrite(STDOUT, "  command [options] [arguments]\n\n");
        fwrite(STDOUT, $this->colored("Available commands:\n", static::COLOR_YELLOW));

        // Print commands.
        foreach ($this->commands as $group => $commands) {
            $cmdGroup = $this->colored($group, static::COLOR_YELLOW);
            fwrite(STDOUT, sprintf(" %s\n", $cmdGroup));
            foreach ($commands as $command) {
                list($cmd, $params, $handler, $description) = $command;
                $cmd = $this->colored($group . ':' . $cmd, static::COLOR_GREEN);
                fwrite(STDOUT, sprintf("  %s\t\t%s\n", $cmd, $description));
            }
        }
    }

    /**
     * @param string $str
     * @param string $fg
     * @return string
     */
    private function colored(string $str, string $fg): string
    {
        return "\033[" . $fg . "m" . $str . "\033[0m";
    }

    /**
     * @param string $cmd
     * @return string|null
     */
    private function getHandlerFromCommand(string $cmd): ?string
    {
        list($group, $action) = explode(':', $cmd);
        if (!isset($this->commands[$group])) {
            return null;
        }

        foreach ($this->commands[$group] as $command) {
            if ($command[0] === $action) {
                return $command[2];
            }
        }

        return null;
    }

    /**
     * @param string $string
     * @return string
     */
    private function toKebabCase(string $string): string
    {
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $string));
    }
}
