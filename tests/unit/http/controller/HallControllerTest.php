<?php

declare(strict_types=1);

namespace tests\unit\http\controller;

use app\ResponseFactory;
use app\Domain\hall\HallService;
use app\http\controller\HallController;
use app\http\responder\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

class HallControllerTest extends TestCase
{
    public function testAll(): void
    {
        $expectedHalls = [
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
        ];
        $service = $this->getMockBuilder(HallService::class)
            ->disableOriginalConstructor()
            ->setMethods(['findAll', 'count'])
            ->getMock();
        $service->expects($this->once())
            ->method('findAll')
            ->willReturn($expectedHalls);
        $service->expects($this->once())
            ->method('count')
            ->willReturn(count($expectedHalls));

        /** @var $responder JsonResponder */
        $responder = $this->getMockBuilder(JsonResponder::class)
            ->setMethods(['success'])
            ->getMock();
        $responder->expects($this->once())
            ->method('success')
            ->with($expectedHalls, count($expectedHalls))
            ->willReturn(ResponseFactory::fromObject(200, [
                'data' => $expectedHalls,
                'count' => count($expectedHalls),
                'errors' => [],
            ]));

        $request = (new Psr17Factory)->createServerRequest('GET', '/halls');
        $controller = new HallController($service, $responder);
        $response = $controller->all($request);

        // Test instance.
        $this->assertInstanceOf(ResponseInterface::class, $response);

        // Test response code.
        $this->assertEquals(200, $response->getStatusCode());

        // Test body.
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('data', $body);
        $this->assertArrayHasKey('count', $body);
        $this->assertIsArray($body['data']);
        $this->assertEquals(3, $body['count']);
        $this->assertCount(3, $body['data']);
        foreach ($body['data'] as $d) {
            $this->assertArrayHasKey('id', $d);
        }
    }
}
