<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Entities\Client;
use App\Entities\Record;
use App\Http\Controllers\Base\AbstractController;
use App\Http\Resources\Booking\PaymentResource;
use App\Http\Responders\ResponderInterface;
use App\Http\ValidationRequests\Record\FormValidationRequest;
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
class RecordController extends AbstractController
{
    private RecordService $service;

    public function __construct(RecordService $service, ResponderInterface $responder, ValidationService $validator)
    {
        parent::__construct($responder, $validator);
        $this->service = $service;
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
        $pagination = $this->getPagination($request);
        $records = $this->service->paginated($pagination, ['client']);
        $count = $pagination->query !== "" ? count($records) : $this->service->count();

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
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, 'Unprocessable entity.', $errors);
        }
        // Find a record.
        $record = $this->service->find($id);
        if ($record === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Record not found.');
        }

        return $this->responder->success($record);
    }

    /**
     * Get one record by id.
     * GET /records/<id>
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function services(ServerRequestInterface $request): ResponseInterface
    {
        $id = RequestUtils::getPathSegment($request, 2);
        $record = $this->service->find($id);
        if ($record === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Record not found.');
        }
        // Find a record.
        $services = $this->service->services($record);
        return $this->responder->success($services);
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
        return $this->responder->success($this->service->statuses());
    }

    /**
     * Calculate price for reservations.
     * POST /records/price
     * @method POST
     * @param HallService $hallService
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function price(HallService $hallService, ServerRequestInterface $request): ResponseInterface
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
        $hall = $hallService->findByID($record->hall_id, ['id', 'base_price', 'prices']);
        if ($hall === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Hall not found.');
        }
        /// Find a coupon.
        $coupon = null;
        $couponCode = $data['coupon']['code'] ?? null;
        if (!empty($couponCode)) {
            $coupon = $this->service->findCouponByCode($couponCode, ['id', 'factor']);
        }
        // Response with document.
        $paymentDoc = new PaymentResource;
        $paymentDoc->price = $this->service->calculatePrice($record, $hall, $coupon);
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
        $coupon = $this->service->findCouponByCode($code);
        if ($coupon === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Coupon not found.');
        }

        return $this->responder->success($coupon);
    }

    /**
     * Post record.
     * POST /records
     * @method POST
     * @param HallService $hallService
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function create(HallService $hallService, ServerRequestInterface $request): ResponseInterface
    {
        // Validate request and get body.
        $data = $this->validateRequest($request, new FormValidationRequest());

        // Check if hall exists.
        if (!$hallService->isExists($data['hall_id'])) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Hall not found.');
        }

        // Load data.
        $record = new Record;
        $record->load($data, ['hall_id', 'reservations', 'service_ids', 'payment', 'comment']);
        $record->client = new Client;
        $record->client->load($data['client'], ['name', 'phone', 'email']);

        // Save record.
        $record = $this->service->create($record, $data['coupon']['code'] ?? null);
        if ($record === null) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, 'Errors during create.');
        }

        return $this->responder->success($record, 1);
    }

    /**
     * Update record.
     * PATCH /records/<id>
     * @method PATCH
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function update(ServerRequestInterface $request): ResponseInterface
    {
        // Validate request data.
        $data = $this->validateRequest($request);

        // Check if record exists.
        $id = RequestUtils::getPathSegment($request, 2);
        if (!$this->service->isExists($id)) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Record not found.');
        }

        // Load data.
        $record = new Record;
        $record->load($data, ['hall_id', 'reservations', 'service_ids', 'status', 'total', 'comment']);
        $record->id = $id;

        // // Check if hall exists.
        // if (!$this->hallService->isExists($record->hall_id, true)) {
        //     return $this->responder->error(ResponseFactory::NOT_FOUND, ["Hall not found."]);
        // }

        // Update record.
        $record = $this->service->update($record);
        if ($record === null) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, 'Errors during create.');
        }

        return $this->responder->success($record);
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
        $isDeleted = $this->service->destroy($id);
        return $this->responder->success($isDeleted, (int)$isDeleted);
    }
}
