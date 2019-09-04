<?php

declare(strict_types=1);

namespace app\http\controller;

use app\ResponseFactory;
use app\domain\record\RecordService;
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
        // $body = json_decode($request->getBody()->getContents(), true);
        $body = $request->getParsedBody();
        if ($body === null) {
            throw new UprocessableEntityException();
        }
        // if (!isset($body['reservations'])) {
        //     throw new ArgumentMismatchException();
        // }

        return ResponseFactory::fromObject(200, $body);
    }
}
