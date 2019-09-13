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
        $params = $this->getQueryParams($request);
        $record = $this->recordService->findByID($id, $params['include'] ?? []);
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
        // Return errors if validation fails.
        if (!empty($errors)) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, $errors);
        }
        // Find hall.
        $hall = $this->hallService->findByID($body->hall_id, ['id', 'base_price', 'prices']);
        if ($hall === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, ['Hall not found.']);
        }
        // Compose record entity.
        $record = new Record;
        $record->hall_id = $hall->id;
        $record->reservations = $body->reservations;
        $record->service_ids = $body->service_ids;

        // Response with document. 
        $bookingDoc = new BookingDocument;
        $bookingDoc->price = $this->recordService->calculatePrice($record, $hall);
        // $bookingDoc->prepayment = $bookingDoc->price * 0.5;
        return $this->responder->success($bookingDoc);
    }
}
