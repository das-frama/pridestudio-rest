<?php

declare(strict_types=1);

namespace app\http\controller;

use app\RequestUtils;
use app\ResponseFactory;
use app\domain\setting\SettingService;
use app\http\exception\RouteNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Settings class.
 */
class SettingsController
{
    /** @var SettingService */
    private $service;

    /**
     * SettingsController constructor.
     * @param SettingService $service
     */
    public function __construct(SettingService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all stored settings.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function all(ServerRequestInterface $request): ResponseInterface
    {
        $settings = $this->service->findAll();
        return ResponseFactory::fromObject(200, $settings);
    }

    /**
     * Get settings by specific group.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function group(ServerRequestInterface $request): ResponseInterface
    {
        $group = RequestUtils::getPathSegment($request, 3);
        $settings = $this->service->findByGroup($group);
        return ResponseFactory::fromObject(200, $settings);
    }

    /**
     * Get one setting by key.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function read(ServerRequestInterface $request): ResponseInterface
    {
        $key = RequestUtils::getPathSegment($request, 2);
        $setting = $this->service->findByKey($key);
        if ($setting === null) {
            throw new RouteNotFoundException();
        }

        return ResponseFactory::fromObject(200, $setting);
    }
}
