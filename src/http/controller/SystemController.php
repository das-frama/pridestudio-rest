<?php

declare(strict_types=1);

namespace app\http\controller;

use app\domain\system\SystemService;
use app\http\controller\base\ControllerTrait;
use app\http\responder\ResponderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * SystemController class.
 */
class SystemController
{
    use ControllerTrait;

    /** @var SystemService */
    private $systemService;

    /** @var ResponderInterface */
    private $responder;

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
