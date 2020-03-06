<?php

declare(strict_types=1);

namespace App\Http\Controller\Frontend;

use App\Domain\Calendar\CalendarService;
use App\Domain\Hall\HallService;
use App\Http\Controller\Base\ControllerTrait;
use App\Http\Responder\ResponderInterface;
use App\RequestUtils;
use App\ResponseFactory;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * CalendarController class.
 */
class CalendarController
{
    use ControllerTrait;

    private CalendarService $calendarService;
    private HallService $hallService;
    private ResponderInterface $responder;

    /**
     * CalendarController constructor.
     * @param CalendarService $calendarService
     * @param HallService $hallService
     * @param ResponderInterface $responder
     */
    public function __construct(
        CalendarService $calendarService,
        HallService $hallService,
        ResponderInterface $responder
    )
    {
        $this->calendarService = $calendarService;
        $this->hallService = $hallService;
        $this->responder = $responder;
    }

    /**
     * Get calendar dates by current year and week.
     * GET /calendar/<hall-id>
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $hallID = RequestUtils::getPathSegment($request, 3);
        if ($hallID === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Hall not found.');
        }
        $document = $this->calendarService->weekdays($hallID);
        return $this->responder->success($document, 1);
    }

    /**
     * Get calendar dates by specified year and current week.
     * GET /calendar/<calendar-id>/<year>
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function week(ServerRequestInterface $request): ResponseInterface
    {
        $hallID = RequestUtils::getPathSegment($request, 3);
        if ($hallID === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Hall not found.');
        }
        $year = (int)RequestUtils::getPathSegment($request, 4);
        $document = $this->calendarService->weekdays($hallID, $year);
        return $this->responder->success($document);
    }

    /**
     * Get calendar dates by year and week.
     * GET /calendar/<calendar-id>/<year>/<week>
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function read(ServerRequestInterface $request): ResponseInterface
    {
        $hallID = RequestUtils::getPathSegment($request, 3);
        if ($hallID === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Hall not found.');
        }
        $year = (int)RequestUtils::getPathSegment($request, 4);
        $week = (int)RequestUtils::getPathSegment($request, 5);
        $document = $this->calendarService->weekdays($hallID, $year, $week);
        return $this->responder->success($document);
    }
}
