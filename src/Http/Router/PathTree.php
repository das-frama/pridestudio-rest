<?php

declare(strict_types=1);

namespace App\Http\Router;

class PathTree implements \JsonSerializable
{
    private $tree;

    public function __construct($tree = null)
    {
        if ($tree === null) {
            $tree = $this->newTree();
        }
        $this->tree = $tree;
    }

    public function newTree(): object
    {
        return (object) ['values' => [], 'branches' => (object) []];
    }

    public function put(array $path, $value): void
    {
        $tree = &$this->tree;
        foreach ($path as $key) {
            if (!isset($tree->branches->{$key})) {
                $tree->branches->{$key} = $this->newTree();
            }
            $tree = &$tree->branches->{$key};
        }
        $tree->values[] = $value;
    }

    public function match(array $path): array
    {
        $star = '*';
        $tree = &$this->tree;
        foreach ($path as $key) {
            if (isset($tree->branches->{$key})) {
                $tree = &$tree->branches->{$key};
            } elseif (isset($tree->branches->{$star})) {
                $tree = &$tree->branches->{$star};
            } else {
                return [];
            }
        }

        return $tree->values;
    }

    public function jsonSerialize(): object
    {
        return $this->tree;
    }
}
