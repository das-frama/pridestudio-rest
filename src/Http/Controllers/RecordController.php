<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Entities\Hall;
use App\Entities\Record;
use App\Http\Controllers\Base\ResourceController;
use App\Http\Resources\Booking\PaymentResource;
use App\Http\Responders\ResponderInterface;
use App\Repositories\ClientRepositoryInterface;
use App\Repositories\RecordRepositoryInterface;
use App\RequestUtils;
use App\ResponseFactory;
use App\Services\HallService;
use App\Services\RecordService;
use App\Services\ValidationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * RecordController class.
 */
class RecordController extends ResourceController
{
    /**
     * RecordController constructor.
     * @param RecordRepositoryInterface $repo
     * @param ClientRepositoryInterface $clientRepo
     * @param ResponderInterface $responder
     * @param ValidationService $validator
     */
    public function __construct(
        RecordRepositoryInterface $repo,
        ClientRepositoryInterface $clientRepo,
        ResponderInterface $responder,
        ValidationService $validator
    ) {
        parent::__construct(Record::class, $repo, $responder, $validator);
        $this->validation['create'] = [
            'client_id' => ['required', 'objectId'],
            'hall_id' => ['required', 'objectId'],
            'reservations' => ['required', 'array'],
            'reservations.$.start_at' => ['required', 'int'],
            'reservations.$.length' => ['required', 'int:0:1440'],
//            'reservations.$.comment' => ['string:0:255'],
            'service_ids' => ['array'],
            'total' => ['int'],
            'status' => ['required', 'int:0:10'],
            'comment' => ['string'],
        ];
        $this->validation['update'] = [
            'client_id' => ['objectId'],
            'hall_id' => ['objectId'],
            'reservations' => ['array'],
            'reservations.$.start_at' => ['int'],
            'reservations.$.length' => ['int:0:1440'],
            'service_ids' => ['array'],
            'total' => ['int'],
            'status' => ['int:0:10'],
            'comment' => ['string'],
        ];
        $this->with['all'] = $this->with['read'] = [
            'client' => ['client_id', $clientRepo],
        ];
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
     * @param RecordService $recordService
     * @param HallService $hallService
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function price(
        RecordService $recordService,
        HallService $hallService,
        ServerRequestInterface $request
    ): ResponseInterface {
        // Get body from request.
        $data = $this->validateRequest($request, $this->validation['update']);
        // Load data from request.
        $record = new Record;
        $record->load($data, ['hall_id', 'reservations', 'service_ids', 'payment_id', 'comment']);
        // Find hall.
        $hall = $hallService->find($record->hall_id);
        if (!($hall instanceof Hall)) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Hall not found.');
        }
        /// Find a coupon.
        $coupon = null;
        $couponCode = $data['coupon']['code'] ?? null;
        if (!empty($couponCode)) {
            $coupon = $recordService->findCouponByCode($couponCode);
        }
        // Response with document.
        $payment = new PaymentResource;
        $payment->price = $recordService->calculatePrice($record, $hall, $coupon);
        // $bookingDoc->prepayment = $bookingDoc->price * 0.5;
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
