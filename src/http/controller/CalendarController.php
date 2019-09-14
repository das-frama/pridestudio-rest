<?php

declare(strict_types=1);

namespace app\http\controller;

use app\RequestUtils;
use app\ResponseFactory;
use app\domain\calendar\CalendarService;
use app\domain\hall\HallService;
use app\http\controller\base\ControllerTrait;
use app\http\responder\ResponderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * CalendarController class.
 */
class CalendarController
{
    use ControllerTrait;

    /** @var CalendarService */
    private $calendarService;

    /** @var HallService */
    private $hallService;

    /** @var ResponderInterface */
    private $reponder;

    /**
     * CalendarController constructor.
     * @param CalendarService $calendarService
     * @param HallService $hallService
     * @param ResponderInterface $responder
     */
    public function __construct(CalendarService $calendarService, HallService $hallService, ResponderInterface $responder)
    {
        $this->calendarService = $calendarService;
        $this->hallService = $hallService;
        $this->responder = $responder;
    }

    /**
     * Get calendar dates by current year and week.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $hallSlug = RequestUtils::getPathSegment($request, 2);
        $hallID = $this->hallService->getIDBySlug($hallSlug);
        if ($hallID === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, ["Hall not found."]);
        }
        $document = $this->calendarService->weekdays($hallID);
        return $this->responder->success($document);
    }

    /**
     * Get calendar dates by specified year and current week.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function week(ServerRequestInterface $request): ResponseInterface
    {
        $hallSlug = RequestUtils::getPathSegment($request, 2);
        $hallID = $this->hallService->getIDBySlug($hallSlug);
        if ($hallID === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, ["Hall not found."]);
        }
        $year = (int) RequestUtils::getPathSegment($request, 3);
        $document = $this->calendarService->weekdays($hallID, $year);
        return $this->responder->success($document);
    }

    /**
     * Get calendar dates by year and week.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function read(ServerRequestInterface $request): ResponseInterface
    {
        $hallSlug = RequestUtils::getPathSegment($request, 2);
        $hallID = $this->hallService->getIDBySlug($hallSlug);
        if ($hallID === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, ["Hall not found."]);
        }
        $year = (int) RequestUtils::getPathSegment($request, 3);
        $week = (int) RequestUtils::getPathSegment($request, 4);
        $document = $this->calendarService->weekdays($hallID, $year, $week);
        return $this->responder->success($document);
    }
}
