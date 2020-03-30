<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Entities\Record;
use App\Http\Controllers\Base\ResourceController;
use App\Http\Requests\Record\PriceRequest;
use App\Http\Resources\Booking\PaymentResource;
use App\Http\Responders\ResponderInterface;
use App\Repositories\ClientRepositoryInterface;
use App\Repositories\RecordRepositoryInterface;
use App\RequestUtils;
use App\ResponseFactory;
use App\Services\RecordService;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * RecordController class.
 */
class RecordController extends ResourceController
{
    protected RecordService $service;

    /**
     * RecordController constructor.
     * @param RecordRepositoryInterface $repo
     * @param ClientRepositoryInterface $clientRepo
     * @param ResponderInterface $responder
     */
    public function __construct(
        RecordService $service,
        RecordRepositoryInterface $repo,
        ClientRepositoryInterface $clientRepo,
        ResponderInterface $responder
    ) {
        parent::__construct(Record::class, $repo, $responder);
        $this->service = $service;
    }

    /**
     * Get one record by id.
     * GET /records/<id>
     * @method GET
     * @param RecordService $service
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function services(RecordService $service, ServerRequestInterface $request): ResponseInterface
    {
        $id = RequestUtils::getPathSegment($request, 2);
        $record = $service->find($id);
        if ($record === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Record not found.');
        }
        // Find a record.
        $services = $service->services($record);
        return $this->responder->success($services);
    }

    /**
     * Get statuses list of records.
     * GET /records/statuses
     * @method GET
     * @param RecordService $service
     * @return ResponseInterface
     */
    public function statuses(RecordService $service): ResponseInterface
    {
        return $this->responder->success($service->statuses());
    }

    /**
     * Calculate price for reservations.
     * POST /records/price
     * @method POST
     * @param PriceRequest $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function price(PriceRequest $request): ResponseInterface {
        // Prepare record.
        $record = new Record($request->toArray());
        $hall = $this->service->findHall($record);
        if ($hall === null) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, 'Hall not found.');
        }
        $coupon = null;
        if (isset($request->coupon)) {
            $coupon = $this->service->findCouponByCode($request->coupon);
        }
        $payment = new PaymentResource([
            'price' => $this->service->calculatePrice($record, $hall, $coupon),
        ]);
        return $this->responder->success($payment);
    }

    /**
     * Check coupon.
     * @method GET
     * @param RecordService $service
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function coupon(RecordService $service, ServerRequestInterface $request): ResponseInterface
    {
        $code = RequestUtils::getPathSegment($request, 3);
        $coupon = $service->findCouponByCode($code);
        if ($coupon === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Coupon not found.');
        }

        return $this->responder->success($coupon);
    }
}
