<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Entities\Hall;
use App\Http\Controllers\Base\ResourceController;
use App\Http\Requests\Hall\FormRequest;
use App\Http\Responders\ResponderInterface;
use App\Repositories\HallRepositoryInterface;
use App\RequestUtils;
use App\ResponseFactory;
use App\Services\HallService;
use App\Services\ValidationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class HallController
 * @package App\Http\Controllers
 */
class HallController extends ResourceController
{
    /**
     * HallController constructor.
     * @param HallRepositoryInterface $repo
     * @param ResponderInterface $responder
     * @param ValidationService $validator
     */
    public function __construct(
        HallRepositoryInterface $repo,
        ResponderInterface $responder,
        ValidationService $validator
    ) {
        parent::__construct(Hall::class, FormRequest::class, $repo, $responder, $validator);
    }

    /**
     * Get services from hall.
     * @method GET
     * @param HallService $service
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function services(HallService $service, ServerRequestInterface $request): ResponseInterface
    {
        $id = RequestUtils::getPathSegment($request, 2);
        if (!$service->isExists($id)) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Hall not found.');
        }
        $selected = $this->getQueryParams($request, 'selected');
        $services = $service->findServices($id, $selected);
        return $this->responder->success($services);
    }
}
