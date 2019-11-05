<?php

declare(strict_types=1);

namespace app\http\controller;

use app\http\controller\base\ControllerTrait;
use app\http\responder\ResponderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class HomeController
 * @package app\controller
 */
class HomeController
{
    use ControllerTrait;

    /** @var ResponderInterface */
    private $responder;

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
        $wisdom = require(APP_DIR . '/data/thoughts/wisdom.php');
        shuffle($wisdom);
        return $this->responder->success($wisdom[0], 1);
    }
}
