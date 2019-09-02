<?php

declare(strict_types=1);

namespace app\http\controller;

use app\RequestUtils;
use app\ResponseFactory;
use app\domain\system\SystemService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * SystemController class.
 */
class SystemController
{
    /** @var SystemService */
    private $service;

    /**
     * SystemController constructor.
     * @param SystemService $service
     */
    public function __construct(SystemService $service)
    {
        $this->service = $service;
    }

    /**
     * Init all settings.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function init(ServerRequestInterface $request): ResponseInterface
    {
        $system = [];
        // Init settings.
        $system['settings'] = $this->service->initSettings();

        return ResponseFactory::fromObject(200, $system);
    }
}
