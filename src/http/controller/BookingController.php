<?php

declare(strict_types=1);

namespace app\http\controller;

use app\ResponseFactory;
use app\domain\booking\BookingDocument;
use app\domain\record\RecordService;
use app\domain\validation\ValidationService;
use app\entity\Record;
use app\entity\Reservation;
use app\http\exception\ArgumentMismatchException;
use app\http\exception\UprocessableEntityException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * BookingController class.
 */
class BookingController
{
    /**
     * @var RecordService
     */
    private $recordService;

    public function __construct(RecordService $recordService)
    {
        $this->recordService = $recordService;
    }

    /**
     * Calculate the price for user reservations.
     * @method POST
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function price(ServerRequestInterface $request): ResponseInterface
    {
        // Load input.
        $body = $request->getParsedBody();
        if ($body === null) {
            throw new UprocessableEntityException();
        }
        // Check existance.
        // if (!isset($body->reservations) || !isset($body->hall_id)) {
        //     throw new ArgumentMismatchException();
        // }

        // Validate input.
        $validator = new ValidationService;
        $errors = $validator->validate($body, [
            'reservations' => ['required', 'array'],
            'reservations.$.start_at' => ['required', 'int'],
            'reservations.$.length' => ['required', 'int'],
            'hall_id' => ['required', 'string:24'],
        ]);
        if (!empty($errors)) {
            return ResponseFactory::fromObject(200, $errors);
        }

        $bookingDoc = new BookingDocument;
        $bookingDoc->price = $this->recordService->calculatePrice($body->hallID, $body->reservations);
        return ResponseFactory::fromObject(200, $bookingDoc);
    }
}
