<?php

declare(strict_types=1);

namespace app\http\controller;

use app\RequestUtils;
use app\ResponseFactory;
use app\domain\setting\SettingService;
use app\http\controller\base\ControllerTrait;
use app\http\responder\ResponderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * SettingsController class.
 */
class SettingsController
{
    use ControllerTrait;

    /** @var SettingService */
    private $settingService;

    /** @var ResponderInterface */
    private $responder;

    /**
     * SettingsController constructor.
     * @param SettingService $service
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
        $settings = $this->settingService->findAll($params['include'] ?? []);
        return $this->responder->success($settings);
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
            return $this->responder->error(ResponseFactory::NOT_FOUND, ['Setting not found.']);
        }
        return $this->responder->success($setting);
    }
}
        // Return errors if validation fails.
