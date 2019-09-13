<?php

declare(strict_types=1);

namespace app\http\controller;

use app\RequestUtils;
use app\ResponseFactory;
use app\entity\Record;
use app\domain\record\RecordService;
use app\domain\booking\BookingDocument;
use app\domain\hall\HallService;
use app\domain\validation\ValidationService;
use app\http\controller\base\ControllerTrait;
use app\http\responder\JsonResponder;
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

    /** @var JsonResponder */
    private $responder;

    public function __construct(RecordService $recordService, HallService $hallService, JsonResponder $responder)
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
        $records = $this->recordService->findAll(0, 0);
        return $this->responder->success($records);
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
        $record = $this->recordService->findByID($id);
        if ($record === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, ["Record not found."]);
        }
        return $this->responder->success($record);
    }

    /**
     * Calculate price for reservations.
     * @method POST
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function price(ServerRequestInterface $request): ResponseInterface
    {
        // Get body from request.
        $body = $request->getParsedBody();
        if ($body === null) {
            return $this->responder->error(ResponseFactory::BAD_REQUEST, ["Very bad request."]);
        }
        // Validate data.
        $validator = new ValidationService;
        $errors = $validator->validate($body, [
            'reservations' => ['required', 'array'],
            'reservations.$.start_at' => ['required', 'int'],
            'reservations.$.length' => ['required', 'int'],
            'service_ids' => ['required', 'array'],
            'service_ids.$' => ['required', 'string:24:24'],
            'hall_id' => ['required', 'string:24:24'],
        ]);
        // Throw exception if there are errors due to validation.
        if (!empty($errors)) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, $errors);
        }
        // Find hall.
        $hall = $this->hallService->findByID($body->hall_id, [
            'include' => '_id,base_price,prices'
        ]);
        if ($hall === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, ['Hall not found.']);
        }
        // Compose record entity.
        $record = new Record;
        $record->hall_id = $hall->id;
        $record->hall = $hall;
        $record->reservations = $body->reservations;
        $record->service_ids = $body->service_ids;

        // Response with document. 
        $bookingDoc = new BookingDocument;
        $bookingDoc->price = $this->recordService->calculatePrice($record);
        // $bookingDoc->prepayment = $bookingDoc->price * 0.5;
        return $this->responder->success($bookingDoc);
    }
}
