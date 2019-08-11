<?php

declare(strict_types=1);

namespace app\http\controller;

use app\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class HomeController
 * @package app\controller
 */
class HomeController
{
    /**
     * {@inheritDoc}
     */
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        return ResponseFactory::fromObject(200, ['success' => true]);
    }
}
