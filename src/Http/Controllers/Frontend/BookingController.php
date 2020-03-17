<?php
declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Base\AbstractController;
use App\Http\Responders\ResponderInterface;
use App\RequestUtils;
use App\ResponseFactory;
use App\Services\BookingService;
use App\Services\ValidationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class BookingController extends AbstractController
{
    protected BookingService $service;
//    private SettingService $settingService;
//    private HallService $hallService;
//    private CalendarService $calendarService;
//    private ResponderInterface $responder;

    /**
     * BookingController constructor.
     * @param BookingService $service
     * @param ResponderInterface $responder
     * @param ValidationService $validator
     */
    public function __construct(BookingService $service, ResponderInterface $responder, ValidationService $validator)
    {
        parent::__construct($responder, $validator);
        $this->service = $service;
//        $this->settingService = $settingService;
//        $this->hallService = $hallService;
//        $this->calendarService = $calendarService;
    }

//    /**
//     * Get all info at once for booking page.
//     * GET /frontend/booking
//     * @method GET
//     * @param ServerRequestInterface $request
//     * @return ResponseInterface
//     * @throws Exception
//     */
//    public function index(ServerRequestInterface $request): ResponseInterface
//    {
//        // Find out default hall.
//        $settings = $this->settingService->findByGroup('calendar', ['key', 'value']);
//        $settings = array_column($settings, 'value', 'key');
//        $hallSlug = $settings['calendar_default_hall'];
//
//        // Find hall.
//        $hall = $this->hallService->findBySlug($hallSlug, ['id', 'name', 'slug', 'preview_image']);
//        if ($hall === null) {
//            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Hall not found.');
//        }
//
//        // Form document.
//        $bookingDoc = new BookingResource;
//        $bookingDoc->settings = $settings;
//        $bookingDoc->hall = $hall;
//        $bookingDoc->services = $this->hallService->findServices($hall->id, [], ['id', 'name', 'children']);
//        $bookingDoc->calendar = $this->calendarService->weekdays($hall->id);
//
//        return $this->responder->success($bookingDoc, 1);
//    }
//
//    /**
//     * Get all info at once for booking page with hall.
//     * GET /frontend/booking/<hall-id>
//     * @method GET
//     * @param ServerRequestInterface $request
//     * @return ResponseInterface
//     * @throws Exception
//     */
//    public function hall(ServerRequestInterface $request): ResponseInterface
//    {
//        // Find hall.
//        $hallSlug = RequestUtils::getPathSegment($request, 3);
//        $hall = $this->hallService->findBySlug($hallSlug, ['id', 'name', 'slug', 'preview_image']);
//        if ($hall === null) {
//            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Hall not found.');
//        }
//
//        // Find settings.
//        $settings = $this->settingService->findByGroup('calendar', ['key', 'value']);
//        $settings = array_column($settings, 'value', 'key');
//
//        // Form document.
//        $bookingDoc = new BookingResource;
//        $bookingDoc->settings = $settings;
//        $bookingDoc->hall = $hall;
//        $bookingDoc->services = $this->hallService->findServices($hall->id, [], ['id', 'name', 'children']);
//        $bookingDoc->calendar = $this->calendarService->weekdays($hall->id);
//
//        return $this->responder->success($bookingDoc);
//    }

    /**
     * Check coupon.
     * GET /frontend/booking/coupon/<code>
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function coupon(ServerRequestInterface $request): ResponseInterface
    {
        $code = RequestUtils::getPathSegment($request, 4);
        $coupon = $this->service->findCoupon($code);
        if ($coupon === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Coupon not found.');
        }
        return $this->responder->success($coupon);
    }
}
