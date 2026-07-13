<?php

namespace Tests\Unit;

use App\Services\DeliveryCoverageService;
use PHPUnit\Framework\TestCase;

class DeliveryCoverageServiceTest extends TestCase
{
    public function test_evaluate_allows_order_when_restriction_is_disabled(): void
    {
        $service = new DeliveryCoverageService;

        $result = $service->evaluate(0.0, 0.0, [
            'enabled' => false,
            'max_radius_km' => 5,
            'store_latitude' => -7.7956,
            'store_longitude' => 110.3695,
        ]);

        $this->assertFalse($result['active']);
        $this->assertTrue($result['within_coverage']);
    }

    public function test_evaluate_blocks_order_when_distance_exceeds_radius(): void
    {
        $service = new DeliveryCoverageService;

        $settings = [
            'enabled' => true,
            'max_radius_km' => 3,
            'store_latitude' => -7.7956,
            'store_longitude' => 110.3695,
        ];

        $result = $service->evaluate(-7.7506, 110.3695, $settings);

        $this->assertTrue($result['active']);
        $this->assertFalse($result['within_coverage']);
        $this->assertSame(DeliveryCoverageService::OUT_OF_COVERAGE_MESSAGE, $result['message']);
    }
}
