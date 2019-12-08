<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Domain\System\SystemService;
use App\Http\Controller\Base\ControllerTrait;
use App\Http\Responder\ResponderInterface;
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
            'settings' => $this->service->initSettings()
        ];
        return $this->responder->success($system);
    }
}
