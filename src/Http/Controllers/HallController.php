<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Entities\Hall;
use App\Http\Controllers\Base\ResourceController;
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
        $this->validation['create'] = [
            'name' => ['required', 'string:1:64'],
            'slug' => ['required', 'string:1:64'],
            'preview_image' => ['string:1:255'],
            'base_price' => ['int:0:999999'],
            'sort' => ['int'],
            'is_active' => ['bool'],
            'services' => ['array:0:50'],
            'services.$.category_id' => ['object_id'],
            'services.$.children' => ['array:0:16'],
            'services.$.children.$' => ['object_id'],
            'services.$.parents' => ['array:0:16'],
            'services.$.parents.$' => ['object_id'],
            'prices' => ['array:0:50'],
            'prices.$.time_from' => ['time'],
            'prices.$.time_to' => ['time'],
            'prices.$.schedule_mask' => ['int:0:127'],
            'prices.$.type' => ['enum:1,2'],
            'prices.$.from_length' => ['int:60:1440'],
            'prices.$.comparison' => ['enum:>,>=,<,<=,=,!='],
            'prices.$.price' => ['int:0:9999999'],
            'prices.$.service_ids' => ['array:0:16'],
            'prices.$.service_ids.$' => ['object_id'],
        ];
        $this->validation['update'] = $this->validation['create'] + [
                'name' => ['string:1:64'],
                'slug' => ['string:1:64'],
            ];
        parent::__construct(Hall::class, $repo, $responder, $validator);
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
