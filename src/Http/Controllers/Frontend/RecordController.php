<?php
declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Entities\Client;
use App\Entities\Record;
use App\Http\Controllers\Base\ControllerTrait;
use App\Http\Resources\Booking\PaymentResource;
use App\Http\Responders\ResponderInterface;
use App\RequestUtils;
use App\ResponseFactory;
use App\Services\HallService;
use App\Services\RecordService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * RecordController class.
 */
class RecordController
{
    use ControllerTrait;

    private RecordService $recordService;
    private HallService $hallService;
    private ResponderInterface $responder;

    public function __construct(RecordService $recordService, HallService $hallService, ResponderInterface $responder)
    {
        $this->recordService = $recordService;
        $this->hallService = $hallService;
        $this->responder = $responder;
    }

    /**
     * Calculate price for reservations.
     * POST /frontend/records/price
     * @method POST
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function price(ServerRequestInterface $request): ResponseInterface
    {
        // Get body from request.
        $data = $request->getParsedBody();
        if (empty($data)) {
            return $this->responder->error(ResponseFactory::BAD_REQUEST, 'Empty body.');
        }
        // Load data from request.
        $record = new Record;
        $record->load($data, ['hall_id', 'reservations', 'service_ids', 'payment_id', 'comment']);
        // Find hall.
        $hall = $this->hallService->findByID($record->hall_id, ['id', 'base_price', 'prices']);
        if ($hall === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Hall not found.');
        }
        /// Find a coupon.
        $coupon = null;
        $couponCode = $data['coupon']['code'] ?? null;
        if (!empty($couponCode)) {
            $coupon = $this->recordService->findCouponByCode($couponCode, ['id', 'factor']);
        }
        // Response with document.
        $paymentDoc = new PaymentResource;
        $paymentDoc->price = $this->recordService->calculatePrice($record, $hall, $coupon);
        // $bookingDoc->prepayment = $bookingDoc->price * 0.5;
        return $this->responder->success($paymentDoc);
    }

    /**
     * Check coupon.
     * GET /frontend/records/coupon/<coupon-code>
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function coupon(ServerRequestInterface $request): ResponseInterface
    {
        $code = RequestUtils::getPathSegment($request, 4);
        $coupon = $this->recordService->findCouponByCode($code, ['code', 'factor']);
        if ($coupon === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Coupon not found.');
        }

        return $this->responder->success($coupon);
    }

    /**
     * Post record.
     * POST /frontend/records
     * @method POST
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        // Get body's data from request.
        $data = $request->getParsedBody();
        if (empty($data)) {
            return $this->responder->error(ResponseFactory::BAD_REQUEST, 'Empty body.');
        }

        // Load data.
        $record = new Record;
        $record->load($data, ['hall_id', 'reservations', 'service_ids', 'comment']);
        $client = new Client;
        $client->load($data['client'], ['name', 'phone', 'email']);

        // Check if hall exists.
        if (!$this->hallService->isExists($record->hall_id, true)) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, "Hall not found.");
        }

        // Save record.
        $record = $this->recordService->create($record, $client, $data['coupon']['code'] ?? null);
        if ($record === null) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, 'Errors during create.');
        }

        return $this->responder->success($record->id, 1, ResponseFactory::CREATED);
    }
}