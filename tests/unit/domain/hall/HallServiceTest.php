<?php

declare(strict_types=1);

namespace tests\unit\Domain\hall;

use app\Domain\hall\HallRepositoryInterface;
use app\Domain\hall\HallService;
use app\entity\Hall;
use app\entity\Service;
use app\entity\ServiceChild;
use PHPUnit\Framework\TestCase;

class HallServiceTest extends TestCase
{
    public function testFindByID()
    {
        $expectedID = '1';
        $expectedHall = new Hall;
        $expectedHall->id = $expectedID;
        $expectedHall->name = 'Test Hall';

        $repository = $this->createMock(HallRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOne')
            ->willReturn($expectedHall);
        $service = new HallService($repository);

        $actualHall = $service->findByID($expectedID);
        $this->assertEquals($expectedHall, $actualHall);
    }

    public function testFindBySlug()
    {
        $expectedSlug = 'the-river';
        $expectedHall = new Hall;
        $expectedHall->slug = $expectedSlug;
        $expectedHall->name = 'Test Hall';

        $repository = $this->createMock(HallRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOne')
            ->willReturn($expectedHall);
        $service = new HallService($repository);

        $actualHall = $service->findBySlug($expectedSlug);
        $this->assertEquals($expectedHall, $actualHall);
    }

    public function testFindServices()
    {
        $expectedServices = [];
        for ($i = 0; $i < 5; $i++) {
            $service = new Service;
            $service->id = uniqid();
            $service->name = 'Test Service';
            for ($j = 0; $j < 3; $j++) {
                $child = new ServiceChild;
                $child->id = uniqid();
                $service->children[] = $child;
            }
            $expectedServices[] = $service;
        }
        $slug = 'fuckme';
        $expectedHall = new Hall;
        $expectedHall->slug = $slug;
        $expectedHall->services = array_map(function (Service $service) {
            return [
                'category_id' => $service->id,
                'children' => array_column($service->children, 'id'),
            ];
        }, $expectedServices);

        $repository = $this->createMock(HallRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOne')
            ->willReturn($expectedHall);
        $service = new HallService($repository);
        $repository->expects($this->once())
            ->method('findServices')
            ->willReturn($expectedServices);
        $service = new HallService($repository);

        $actualServices = $service->findServices($slug);

        $this->assertIsArray($actualServices);
        $this->assertEmpty(array_diff_key($expectedServices, $actualServices));
    }
}
