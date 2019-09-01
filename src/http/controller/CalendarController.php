<?php

declare(strict_types=1);

namespace app\http\controller;

use app\RequestUtils;
use app\ResponseFactory;
use app\domain\calendar\CalendarService;
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
    private $service;

    /**
     * CalendarController constructor.
     * @param CalendarService $service
     */
    public function __construct(CalendarService $service)
    {
        $this->service = $service;
    }

    /**
     * Get calendar dates by current year and week.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $document = $this->service->weekdays();
        return ResponseFactory::fromObject(200, $document);
    }

    /**
     * Get calendar dates by specified year and current week.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function week(ServerRequestInterface $request): ResponseInterface
    {
        $year = (int) RequestUtils::getPathSegment($request, 2);
        $document = $this->service->weekdays($year);

        return ResponseFactory::fromObject(200, $document);
    }

    /**
     * Get calendar dates by year and week.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function read(ServerRequestInterface $request): ResponseInterface
    {
        $year = (int) RequestUtils::getPathSegment($request, 2);
        $week = (int) RequestUtils::getPathSegment($request, 3);
        $document = $this->service->weekdays($year, $week);

        return ResponseFactory::fromObject(200, $document);
    }
}
