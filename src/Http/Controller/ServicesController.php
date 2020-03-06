<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Domain\Service\ServiceService;
use App\Http\Controller\Base\ControllerTrait;
use App\Http\Responder\ResponderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ServicesController
 * @package App\Http\Controller
 */
class ServicesController
{
    use ControllerTrait;

    private ServiceService $serviceService;
    private ResponderInterface $responder;

    /**
     * ServicesController constructor.
     * @param ServiceService $serviceService
     * @param ResponderInterface $responder
     */
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
        $count = isset($params['query']) ? count($halls) : $this->serviceService->count();

        return $this->responder->success($halls, $count);
    }
}
