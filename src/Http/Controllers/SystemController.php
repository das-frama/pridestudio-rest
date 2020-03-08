<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\SystemService;
use App\Http\Controllers\Base\ControllerTrait;
use App\Http\Responders\ResponderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * SystemController class.
 */
class SystemController
{
    use ControllerTrait;

    private SystemService $systemService;
    private ResponderInterface $responder;

    /**
     * SystemController constructor.
     * @param SystemService $systemService
     * @param ResponderInterface $responder
     */
    public function __construct(SystemService $systemService, ResponderInterface $responder)
    {
        $this->systemService = $systemService;
        $this->responder = $responder;
    }

    /**
     * Init all settings.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function init(ServerRequestInterface $request): ResponseInterface
    {
        $system = [
            'settings' => $this->systemService->initSettings([]),
        ];
        return $this->responder->success($system);
    }
}
