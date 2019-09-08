<?php

declare(strict_types=1);

namespace app\http\controller;

use app\RequestUtils;
use app\ResponseFactory;
use app\domain\record\RecordService;
use app\domain\booking\BookingDocument;
use app\domain\hall\HallService;
use app\domain\validation\ValidationService;
use app\entity\Record;
use app\http\controller\base\Controller;
use app\http\exception\BadRequestException;
use app\http\exception\ResourceNotFoundException;
use app\http\exception\RouteNotFoundException;
use app\http\exception\UprocessableEntityException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * RecordController class.
 */
class RecordController extends Controller
{
    /** @var RecordService */
    private $recordService;

    /** @var HallService */
    private $hallService;

    public function __construct(
        RecordService $recordService,
        HallService $hallService
    ) {
        $this->recordService = $recordService;
        $this->hallService = $hallService;
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
        return ResponseFactory::fromObject(200, $records);
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
            throw new RouteNotFoundException();
        }

        return ResponseFactory::fromObject(200, $record);
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
            throw new BadRequestException();
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
            $key = array_key_first($errors);
            $message = implode(', ', $errors[$key]);
            throw new UprocessableEntityException($message);
        }
        // Find hall.
        $hall = $this->hallService->findByID($body->hall_id, [
            'include' => '_id,base_price,prices'
        ]);
        if ($hall === null) {
            throw new ResourceNotFoundException("Hall not found.");
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
        return ResponseFactory::fromObject(200, $bookingDoc);
    }
}
