<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Base\ControllerTrait;
use App\Http\Responders\ResponderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class HomeController
 * @package App\Controllers
 */
class HomeController
{
    use ControllerTrait;

    private ResponderInterface $responder;

    public function __construct(ResponderInterface $responder)
    {
        $this->responder = $responder;
    }

    /**
     * Base response.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        return $this->responder->success("Привет. Это API прайдстудио.");
    }
}
