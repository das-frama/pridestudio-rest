<?php
declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Base\AbstractController;
use App\Http\Requests\BookRequest;
use App\Http\Resources\Booking\BookingResource;
use App\Http\Responders\ResponderInterface;
use App\RequestUtils;
use App\ResponseFactory;
use App\Services\BookingService;
use App\Services\CalendarService;
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
    }

    /**
     * Get all info at once for booking page.
     * GET /frontend/booking
     * @method GET
     * @param CalendarService $calendarService
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Exception
     */
    public function index(CalendarService $calendarService, ServerRequestInterface $request): ResponseInterface
    {
        $hallSlug = RequestUtils::getPathSegment($request, 3);
        $hall = $hallSlug ? $this->service->hall($hallSlug) : $this->service->defaultHall();
        if ($hall === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Hall not found.');
        }

        // Form document.
        $booking = new BookingResource([
            'hall' => $hall,
            'settings' => $this->service->settings('calendar'),
            'services' => $this->service->services($hall->id),
            'calendar' => $calendarService->weekdays($hall->id),
        ]);

        return $this->responder->success($booking);
    }
}
