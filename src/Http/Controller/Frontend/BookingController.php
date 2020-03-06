<?php

declare(strict_types=1);

namespace App\Http\Controller\Frontend;

use App\Domain\Booking\BookingDocument;
use App\Domain\Calendar\CalendarService;
use App\Domain\Hall\HallService;
use App\Domain\Setting\SettingService;
use App\Http\Controller\Base\ControllerTrait;
use App\Http\Responder\ResponderInterface;
use App\RequestUtils;
use App\ResponseFactory;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class BookingController
{
    use ControllerTrait;

    private SettingService $settingService;
    private HallService $hallService;
    private CalendarService $calendarService;
    private ResponderInterface $responder;

    /**
     * BookingController constructor.
     * @param SettingService $settingService
     * @param HallService $hallService
     * @param CalendarService $calendarService
     * @param ResponderInterface $responder
     */
    public function __construct(
        SettingService $settingService,
        HallService $hallService,
        CalendarService $calendarService,
        ResponderInterface $responder
    )
    {
        $this->settingService = $settingService;
        $this->hallService = $hallService;
        $this->calendarService = $calendarService;
        $this->responder = $responder;
    }

    /**
     * Get all info at once for booking page.
     * GET /frontend/booking
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
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
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Hall not found.');
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
     * GET /frontend/booking/<hall-id>
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function hall(ServerRequestInterface $request): ResponseInterface
    {
        // Find hall.
        $hallSlug = RequestUtils::getPathSegment($request, 3);
        $hall = $this->hallService->findBySlug($hallSlug, ['id', 'name', 'slug', 'preview_image']);
        if ($hall === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Hall not found.');
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
