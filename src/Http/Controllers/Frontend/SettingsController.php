<?php
declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Base\ControllerTrait;
use App\Http\Responders\ResponderInterface;
use App\RequestUtils;
use App\ResponseFactory;
use App\Services\SettingService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * SettingsController class.
 */
class SettingsController
{
    use ControllerTrait;

    private SettingService $settingService;
    private ResponderInterface $responder;

    /**
     * SettingsController constructor.
     * @param SettingService $settingService
     * @param ResponderInterface $responder
     */
    public function __construct(SettingService $settingService, ResponderInterface $responder)
    {
        $this->settingService = $settingService;
        $this->responder = $responder;
    }

    /**
     * Get all stored settings.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function all(ServerRequestInterface $request): ResponseInterface
    {
        $params = $this->getQueryParams($request);
        $include = $params['include'] ?? [];
        $settings = $this->settingService->findAll($params, $include);
        $count = isset($params['query']) ? count($settings) : $this->settingService->count();
        return $this->responder->success($settings, $count);
    }

    /**
     * Get settings by specific group.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function group(ServerRequestInterface $request): ResponseInterface
    {
        $group = RequestUtils::getPathSegment($request, 3);
        $params = $this->getQueryParams($request);
        $settings = $this->settingService->findByGroup($group, $params['include'] ?? []);
        return $this->responder->success($settings);
    }

    /**
     * Get one setting by key.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function read(ServerRequestInterface $request): ResponseInterface
    {
        $key = RequestUtils::getPathSegment($request, 2);
        $params = $this->getQueryParams($request);
        $setting = $this->settingService->findByKey($key, $params['include'] ?? []);
        if ($setting === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Setting not found.');
        }
        return $this->responder->success($setting);
    }
}

