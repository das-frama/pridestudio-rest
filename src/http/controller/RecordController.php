<?php

declare(strict_types=1);

namespace app\http\controller;

use app\RequestUtils;
use app\ResponseFactory;
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
     * GET /records
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function all(ServerRequestInterface $request): ResponseInterface
    {
        $params = $this->getQueryParams($request);
        $records = $this->recordService->findAll($params['include'] ?? []);
        return $this->responder->success($records);
    }

    /**
     * Get one record by id.
     * GET /records/<id>
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
        if (empty($body)) {
            return $this->responder->error(ResponseFactory::BAD_REQUEST, ["Empty body."]);
        }
        // Load data from request.
        $record = $this->recordService->load($body);
        // Find hall.
        $hall = $this->hallService->findByID($record->hall_id, ['id', 'base_price', 'prices']);
        if ($hall === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, ['Hall not found.']);
        }
        /// Find a coupon.
        $coupon = null;
        $couponCode = $body['coupon'] ?? null;
        if ($couponCode !== null) {
            $coupon = $this->recordService->findCouponByCode($couponCode, ['id', 'factor']);
        }
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
