<?php

declare(strict_types=1);

namespace app\http\controller;

use app\domain\service\ServiceService;
use app\http\controller\base\ControllerTrait;
use app\http\responder\ResponderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ServicesController
 * @package app\http\controller
 */
class ServicesController
{
    use ControllerTrait;

    /** @var ServiceService */
    private $serviceService;

    /** @var ResponderInterface */
    private $responder;

    public function __construct(ServiceService $serviceService, ResponderInterface $responder)
    {
        $this->serviceService = $serviceService;
        $this->responder = $responder;
    }

    /**
     * List all services.
     * GET /services
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function all(ServerRequestInterface $request): ResponseInterface
    {
        $params = $this->getQueryParams($request);
        $include = $params['include'] ?? [];
        $halls = $this->serviceService->findAll($params, $include);
        $count = isset($params['query']) ? count($halls) : $this->hallService->count();

        return $this->responder->success(['success' => true]);
    }
}
