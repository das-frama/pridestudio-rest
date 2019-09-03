<?php

declare(strict_types=1);

namespace app\http\controller;

use app\RequestUtils;
use app\ResponseFactory;
use app\domain\calendar\CalendarService;
use app\domain\hall\HallService;
use app\http\exception\RouteNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Calendar class.
 */
class CalendarController
{
    public $includeColumns = [];
    public $excludeColumns = ['created_by', 'updated_by', 'created_at', 'updated_at', 'is_active'];

    /** @var CalendarService */
    private $calendarService;

    /** @var HallService */
    private $hallService;

    /**
     * CalendarController constructor.
     * @param CalendarService $calendarService
     * @param HallService $hallService
     */
    public function __construct(CalendarService $calendarService, HallService $hallService)
    {
        $this->calendarService = $calendarService;
        $this->hallService = $hallService;
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
            throw new RouteNotFoundException();
        }
        $document = $this->calendarService->weekdays($hallID);
        return ResponseFactory::fromObject(200, $document);
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
            throw new RouteNotFoundException();
        }
        $year = (int) RequestUtils::getPathSegment($request, 3);
        $document = $this->calendarService->weekdays($hallID, $year);

        return ResponseFactory::fromObject(200, $document);
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
            throw new RouteNotFoundException();
        }
        $year = (int) RequestUtils::getPathSegment($request, 3);
        $week = (int) RequestUtils::getPathSegment($request, 4);
        $document = $this->calendarService->weekdays($hallID, $year, $week);

        return ResponseFactory::fromObject(200, $document);
    }
}
