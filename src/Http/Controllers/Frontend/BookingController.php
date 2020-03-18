<?php
declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Entities\Client;
use App\Entities\Coupon;
use App\Entities\Record;
use App\Http\Controllers\Base\AbstractController;
use App\Http\Requests\BookRequest;
use App\Http\Responders\ResponderInterface;
use App\RequestUtils;
use App\ResponseFactory;
use App\Services\BookingService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class BookingController
 * @package App\Http\Controllers\Frontend
 */
class BookingController extends AbstractController
{
    protected BookingService $service;

    /**
     * BookingController constructor.
     * @param BookingService $service
     * @param ResponderInterface $responder
     */
    public function __construct(BookingService $service, ResponderInterface $responder)
    {
        parent::__construct($responder);
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
}
