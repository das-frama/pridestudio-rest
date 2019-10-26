<?php

declare(strict_types=1);

namespace app\http\controller;

use app\RequestUtils;
use app\ResponseFactory;
use app\entity\Record;
use app\domain\record\RecordService;
use app\domain\booking\PaymentDocument;
use app\domain\hall\HallService;
use app\domain\validation\ValidationService;
use app\http\controller\base\ControllerTrait;
use app\http\responder\ResponderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * RecordController class.
 */
class RecordController
{
    use ControllerTrait;

    /** @var RecordService */
    private $recordService;

    /** @var HallService */
    private $hallService;

    /** @var ResponderInterface */
    private $responder;

    public function __construct(RecordService $recordService, HallService $hallService, ResponderInterface $responder)
    {
        $this->recordService = $recordService;
        $this->hallService = $hallService;
        $this->responder = $responder;
    }

    /**
     * Get all records.
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function all(ServerRequestInterface $request): ResponseInterface
    {
        $params = $this->getQueryParams($request);
        $records = $this->recordService->findAll($params['include'] ?? []);
        $count = isset($params['query']) ? count($records) : $this->recordService->count();
        return $this->responder->success($records, $count);
    }

    /**
     * Get one record by id.
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function read(ServerRequestInterface $request): ResponseInterface
    {
        $id = RequestUtils::getPathSegment($request, 2);
        // Validate id.
        $err = (new ValidationService)->validateMongoId($id);
        if ($err !== null) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, [$err]);
        }
        // Get query params.
        $params = $this->getQueryParams($request);
        // Find a record.
        $record = $this->recordService->findByID($id, $params['include'] ?? []);
        if ($record === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, ["Record not found."]);
        }
        return $this->responder->success($record);
    }

    /**
     * Calculate price for reservations.
     * POST /records/price
     * @method POST
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function price(ServerRequestInterface $request): ResponseInterface
    {
        // Get body from request.
        $body = $request->getParsedBody();
        if ($body === null || $body === []) {
            return $this->responder->error(ResponseFactory::BAD_REQUEST, ["Very bad request."]);
        }
        $validationService = new ValidationService;
        $rules = [
            'reservations' => ['required', 'array:1:24'],
            'reservations.$.start_at' => ['required', 'int'],
            'reservations.$.length' => ['required', 'int'],
            'service_ids' => ['array'],
            'service_ids.$' => ['mongoid'],
            'hall_id' => ['required', 'mongoid'],
            'coupon' => ['string'],
        ];
        // Sanitze incoming data.
        $body = $validationService->sanitize($body, $rules);
        // Validate incoming data.
        $errors = $validationService->validate($body, $rules);
        if ($errors !== []) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, $errors);
        }
        // Find a hall.
        $hall = $this->hallService->findByID($body->hall_id, ['id', 'base_price', 'prices']);
        if ($hall === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, ['Hall not found.']);
        }
        /// Find a coupon.
        $coupon = null;
        if (!empty($body->coupon)) {
            $coupon = $this->recordService->findCouponByCode($body->coupon, ['id', 'factor']);
        }

        // Compose record entity.
        $record = new Record;
        $record->hall_id = $hall->id;
        $record->reservations = $body->reservations;
        $record->service_ids = $body->service_ids;

        // Response with document.
        $paymentDoc = new PaymentDocument;
        $paymentDoc->price = $this->recordService->calculatePrice($record, $hall, $coupon);
        // $bookingDoc->prepayment = $bookingDoc->price * 0.5;
        return $this->responder->success($paymentDoc);
    }

    /**
     * Check coupon.
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function coupon(ServerRequestInterface $request): ResponseInterface
    {
        $code = RequestUtils::getPathSegment($request, 3);
        $coupon = $this->recordService->findCouponByCode($code, ['code', 'factor']);
        if ($coupon === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, ['Coupon not found.']);
        }

        return $this->responder->success($coupon);
    }
}
