<?php
declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Entities\Client;
use App\Entities\Coupon;
use App\Entities\Record;
use App\Http\Controllers\Base\AbstractController;
use App\Http\Requests\Booking\BookRequest;
use App\Http\Requests\Booking\PriceRequest;
use App\Http\Resources\Booking\PaymentResource;
use App\Http\Responders\ResponderInterface;
use App\RequestUtils;
use App\ResponseFactory;
use App\Services\RecordService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * RecordController class.
 */
class RecordController extends AbstractController
{
    protected RecordService $service;

    /**
     * RecordController constructor.
     * @param RecordService $service
     * @param ResponderInterface $responder
     */
    public function __construct(RecordService $service, ResponderInterface $responder)
    {
        $this->service = $service;
        parent::__construct($responder);
    }

    /**
     * Book.
     * @method POST
     * @param BookRequest $request
     * @return ResponseInterface
     */
    public function create(BookRequest $request): ResponseInterface
    {
        // Prepare entities.
        $record = new Record($request->toArray());
        $client = new Client($request->client);
        $coupon = null;
        if (isset($request->coupon)) {
            $coupon = new Coupon([
                'code' => $request->coupon,
            ]);
        }

        $record = $this->service->book($record, $client, $coupon);
        if ($record === null) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, 'Could not store record.');
        }

        return $this->responder->success($record);
    }

    /**
     * Calculate price for reservations.
     * POST /frontend/records/price
     * @method POST
     * @param PriceRequest $request
     * @return ResponseInterface
     * @throws \Exception
     */
    public function price(PriceRequest $request): ResponseInterface
    {
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
     * GET /frontend/booking/coupon/<code>
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function coupon(ServerRequestInterface $request): ResponseInterface
    {
        $code = RequestUtils::getPathSegment($request, 4);
        $coupon = $this->service->findCouponByCode($code);
        if ($coupon === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Coupon not found.');
        }
        return $this->responder->success($coupon);
    }
}
