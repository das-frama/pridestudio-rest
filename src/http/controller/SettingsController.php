<?php

declare(strict_types=1);

namespace app\http\controller;

use app\RequestUtils;
use app\domain\setting\SettingService;
use app\http\controller\base\ControllerTrait;
use app\http\responder\JsonResponder;
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
    public function __construct(SettingService $settingService, JsonResponder $responder)
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
        $settings = $this->settingService->findAll();
        return $this->responder($settings);
    }

    /**
     * Get settings by specific group.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function group(ServerRequestInterface $request): ResponseInterface
    {
        $group = RequestUtils::getPathSegment($request, 3);
        $settings = $this->settingService->findByGroup($group);
        return $this->responder($settings);
    }

    /**
     * Get one setting by key.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function read(ServerRequestInterface $request): ResponseInterface
    {
        $key = RequestUtils::getPathSegment($request, 2);
        $setting = $this->settingService->findByKey($key);
        if ($setting === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, ['Setting not found.']);
        }
        return $this->responder($setting);
    }
}
