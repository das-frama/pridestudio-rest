<?php

declare(strict_types=1);

namespace app\http\controller;

use app\ResponseFactory;
use app\RequestUtils;
use app\domain\booking\BookingDocument;
use app\domain\calendar\CalendarService;
use app\domain\setting\SettingService;
use app\domain\hall\HallService;
use app\http\controller\base\ControllerTrait;
use app\http\responder\ResponderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class BookingController
{
    use ControllerTrait;

    /** @var SettingService */
    private $settingService;

    /** @var HallService */
    private $hallService;

    /** @var CalendarService */
    private $calendarService;

    /** @var ResponderInterface */
    private $responder;

    public function __construct(
        SettingService $settingService,
        HallService $hallService,
        CalendarService $calendarService,
        ResponderInterface $responder
    ) {
        $this->settingService = $settingService;
        $this->hallService = $hallService;
        $this->calendarService = $calendarService;
        $this->responder = $responder;
    }

    /**
     * Get all info at once for booking page.
     * GET /booking
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        // Find out default hall.
        $settings = $this->settingService->findByGroup('calendar', ['key', 'value']);
        $settings = array_column($settings, 'value', 'key');
        $hallSlug = $settings['calendar_default_hall'];

        // Find hall.
        $hall = $this->hallService->findBySlug($hallSlug, ['id', 'name', 'slug', 'preview_image']);
        if ($hall === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, ['Hall not found.']);
        }

        // Form document.
        $bookingDoc = new BookingDocument;
        $bookingDoc->settings = $settings;
        $bookingDoc->hall = $hall;
        $bookingDoc->services = $this->hallService->findServices($hall->id, [], ['id', 'name', 'children']);
        $bookingDoc->calendar = $this->calendarService->weekdays($hall->id);

        return $this->responder->success($bookingDoc, 1);
    }

    /**
     * Get all info at once for booking page with hall.
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function hall(ServerRequestInterface $request): ResponseInterface
    {
        $hallSlug = RequestUtils::getPathSegment($request, 2);
        // Find hall.
        $hall = $this->hallService->findBySlug($hallSlug, ['id', 'name', 'slug', 'preview_image']);
        if ($hall === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, ['Hall not found.']);
        }
        // Find settings.
        $settings = $this->settingService->findByGroup('calendar', ['key', 'value']);
        $settings = array_column($settings, 'value', 'key');
        // Form document.
        $bookingDoc = new BookingDocument;
        $bookingDoc->settings = $settings;
        $bookingDoc->hall = $hall;
        $bookingDoc->services = $this->hallService->findServices($hall->id, [], ['id', 'name', 'children']);
        $bookingDoc->calendar = $this->calendarService->weekdays($hall->id);

        return $this->responder->success($bookingDoc);
    }
}
