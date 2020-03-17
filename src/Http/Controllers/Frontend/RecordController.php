<?php
declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Entities\Record;
use App\Http\Controllers\Base\AbstractController;
use App\Http\Resources\Booking\PaymentResource;
use App\Http\Responders\ResponderInterface;
use App\RequestUtils;
use App\ResponseFactory;
use App\Services\RecordService;
use App\Services\ValidationService;
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
     * @param ValidationService $validator
     */
    public function __construct(RecordService $service, ResponderInterface $responder, ValidationService $validator)
    {
        $this->service = $service;
        parent::__construct($responder, $validator);
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
        $data = $this->validateRequest($request, [
            'hall_id' => ['required', 'objectId'],
            'reservations' => ['required', 'array'],
            'reservations.$.start_at' => ['required', 'int'],
            'reservations.$.length' => ['required', 'int:0:1440'],
            'service_ids' => ['array'],
            'coupon' => ['array'],
            'coupon.code' => ['string'],
        ]);
        // Prepare record.
        $record = new Record($data);
        $record = $this->service->withHall($record);
        if ($record->hall === null) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, 'Hall not exists.');
        }
        $record = $this->service->withCoupon($record);

        $payment = new PaymentResource([
            'price' => $this->service->calculatePrice($record),
        ]);
        return $this->responder->success($payment);
    }

    /**
     * Book.
     * @method POST
     * @param RecordService $service
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        // Validate request.
        $data = $this->validateRequest($request, [
            'hall_id' => ['required', 'objectId'],
            'reservations' => ['required', 'array'],
            'reservations.$.start_at' => ['required', 'int'],
            'reservations.$.length' => ['required', 'int:0:1440'],
            'service_ids' => ['array'],
            'client_comment' => ['string'],
            'client' => ['required'],
            'client.name' => ['required', 'string:3:255'],
            'client.email' => ['required', 'email'],
            'client.phone' => ['required', 'string'],
            'coupon' => ['array'],
            'coupon.code' => ['string'],
        ]);
        // Create and store record.
        $record = $this->service->book(new Record($data));
        if ($record === null) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, 'Could not store record.');
        }

        return $this->responder->success($record);
    }
}
