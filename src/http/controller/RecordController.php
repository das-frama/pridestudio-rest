<?php

declare(strict_types=1);

namespace app\http\controller;

use app\RequestUtils;
use app\ResponseFactory;
use app\entity\Client;
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
     * GET /records
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function all(ServerRequestInterface $request): ResponseInterface
    {
        $params = $this->getQueryParams($request);
        $records = $this->recordService->findAll($params, $params['include'] ?? [], $params['expand'] ?? []);
        $count = isset($params['query']) ? count($records) : $this->recordService->count();
        return $this->responder->success($records, $count);
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
        $errors = (new ValidationService)->validateObjectId($id);
        if ($errors !== []) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, $errors);
        }
        // Get query params.
        $params = $this->getQueryParams($request);
        // Find a record.
        $record = $this->recordService->findByID($id, $params['include'] ?? [], $params['expand'] ?? []);
        if ($record === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, ["Record not found."]);
        }
        
        return $this->responder->success($record);
    }

    /**
     * Get statuses list of records.
     * GET /records/statuses
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function statuses(ServerRequestInterface $request): ResponseInterface
    {
        return $this->responder->success($this->recordService->statuses());
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
        $data = $request->getParsedBody();
        if (empty($data)) {
            return $this->responder->error(ResponseFactory::BAD_REQUEST, ["Empty body."]);
        }
        // Load data from request.
        $record = new Record;
        $record->load($data, ['hall_id', 'reservations', 'service_ids', 'payment_id', 'comment']);
        // Find hall.
        $hall = $this->hallService->findByID($record->hall_id, ['id', 'base_price', 'prices']);
        if ($hall === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, ['Hall not found.']);
        }
        /// Find a coupon.
        $coupon = null;
        $couponCode = $data['coupon']['code'] ?? null;
        if (!empty($couponCode)) {
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

    /**
     * Post record.
     * POST /records
     * @method POST
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        // Get body's data from request.
        $data = $request->getParsedBody();
        if (empty($data)) {
            return $this->responder->error(ResponseFactory::BAD_REQUEST, ['Empty body.']);
        }

        // Load data.
        $record = new Record;
        $record->load($data, ['hall_id', 'reservations', 'service_ids', 'payment', 'comment']);
        $client = new Client;
        $client->load($data['client'], ['name', 'phone', 'email']);

        // Check if hall exists.
        if (!$this->hallService->isExists($record->hall_id, true)) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, ["Hall not found."]);
        }

        // Save record.
        $record = $this->recordService->create($record, $client, $data['coupon']['code'] ?? null);
        if ($record === null) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, ["Errors during create."]);
        }
    
        return $this->responder->success($record, 1);
    }

    /**
     * Update record.
     * PUT /records/<id>
     * @method PUT
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function update(ServerRequestInterface $request): ResponseInterface
    {
        // Check if record exists.
        $id = RequestUtils::getPathSegment($request, 2);
        if (!$this->recordService->isExists($id)) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, ['Record not found.']);
        }

        // Get body's data from request.
        $data = $request->getParsedBody();
        if (empty($data)) {
            return $this->responder->error(ResponseFactory::BAD_REQUEST, ['Empty body.']);
        }

        // Load data.
        $record = new Record;
        $record->load($data, ['hall_id', 'reservations', 'service_ids', 'status', 'total', 'comment']);
        $record->id = $id;
        $client = new Client;
        $client->load($data['client'], ['name', 'phone', 'email']);

        // // Check if hall exists.
        // if (!$this->hallService->isExists($record->hall_id, true)) {
        //     return $this->responder->error(ResponseFactory::NOT_FOUND, ["Hall not found."]);
        // }

        // Update record.
        $record = $this->recordService->update($record, $client);
        if ($record === null) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, ["Errors during create."]);
        }
    
        return $this->responder->success($record, 1);
    }

    /**
     * Delete record.
     * DELETE /records/<id>
     * @method DELETE
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        $id = RequestUtils::getPathSegment($request, 2);
        $isDeleted = $this->recordService->delete($id);
        return $this->responder->success($isDeleted, (int) $isDeleted);
    }
}
