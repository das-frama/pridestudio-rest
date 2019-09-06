<?php

declare(strict_types=1);

namespace app\http\controller;

use app\RequestUtils;
use app\ResponseFactory;
use app\domain\record\RecordService;
use app\domain\booking\BookingDocument;
use app\domain\validation\ValidationService;
use app\http\controller\base\Controller;
use app\http\exception\BadRequestException;
use app\http\exception\RouteNotFoundException;
use app\http\exception\UprocessableEntityException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Record class.
 */
class RecordController extends Controller
{
    /**
     * @var RecordService
     */
    private $service;

    public function __construct(RecordService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all records.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function all(ServerRequestInterface $request): ResponseInterface
    {
        $records = $this->service->findAll(0, 0);
        return ResponseFactory::fromObject(200, $records);
    }

    /**
     * Get one record by id.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function read(ServerRequestInterface $request): ResponseInterface
    {
        $id = RequestUtils::getPathSegment($request, 2);
        $record = $this->service->findByID($id);
        if ($record === null) {
            throw new RouteNotFoundException();
        }

        return ResponseFactory::fromObject(200, $record);
    }

    /**
     * Calculate the price for user reservations.
     * @method POST
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function price(ServerRequestInterface $request): ResponseInterface
    {
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
            'hall_id' => ['required', 'string:24'],
        ]);
        if (!empty($errors)) {
            $key = array_key_first($errors);
            $message = implode(', ', $errors[$key]);
            throw new UprocessableEntityException($message);
        }

        $bookingDoc = new BookingDocument;
        $bookingDoc->price = $this->service->calculatePrice($body->hall_id, $body->reservations);
        return ResponseFactory::fromObject(200, $bookingDoc);
    }
}
