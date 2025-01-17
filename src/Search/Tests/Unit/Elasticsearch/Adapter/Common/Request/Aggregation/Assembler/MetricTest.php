<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @package   Elasticsuite
 * @author    ElasticSuite Team <elasticsuite@smile.fr>
 * @copyright 2022 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

declare(strict_types=1);

namespace Elasticsuite\Search\Tests\Unit\Elasticsearch\Adapter\Common\Request\Aggregation\Assembler;

use Elasticsuite\Search\Elasticsearch\Adapter\Common\Request\Aggregation\Assembler\Metric as MetricAssembler;
use Elasticsuite\Search\Elasticsearch\Request\Aggregation\Metric;
use Elasticsuite\Search\Elasticsearch\Request\MetricInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Search adapter term aggregation assembler test case.
 */
class MetricTest extends KernelTestCase
{
    /**
     * Test the standard metric aggregation building.
     */
    public function testBasicAggregationBuild(): void
    {
        $aggBuilder = $this->getAggregationAssembler();
        $metric = new Metric('aggregationName', 'fieldName');

        $aggregation = $aggBuilder->assembleAggregation($metric);

        $this->assertArrayHasKey('stats', $aggregation);
        $this->assertEquals('fieldName', $aggregation['stats']['field']);
    }

    /**
     * Test invalid metric aggregation building.
     */
    public function testInvalidMetricType(): void
    {
        $aggBuilder = $this->getAggregationAssembler();
        $metric = new Metric('aggregationName', 'fieldName', 'invalidType');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Aggregation assembler : invalid metric type invalidType.');
        $aggBuilder->assembleAggregation($metric);
    }

    /**
     * Test complexe aggregation build.
     */
    public function testComplexeAggregationBuild(): void
    {
        $aggBuilder = $this->getAggregationAssembler();
        $metric = new Metric(
            'aggregationName',
            'fieldName',
            MetricInterface::TYPE_PERCENTILES,
            [
                'interval' => [
                    'percents' => [95, 99, 99.9],
                ],
            ]
        );

        $aggregation = $aggBuilder->assembleAggregation($metric);

        $this->assertArrayHasKey('percentiles', $aggregation);
        $this->assertEquals('fieldName', $aggregation['percentiles']['field']);
        $this->assertEquals(['percents' => [95, 99, 99.9]], $aggregation['percentiles']['interval']);
    }

    /**
     * Test the metric aggregation with script building.
     */
    public function testMetricWithScriptAggregationBuild(): void
    {
        $aggBuilder = $this->getAggregationAssembler();
        $metric = new Metric('aggregationName', 'fieldName', MetricInterface::TYPE_EXTENDED_STATS, ['script' => 'test']);

        $aggregation = $aggBuilder->assembleAggregation($metric);

        $this->assertArrayHasKey('extended_stats', $aggregation);
        $this->assertEquals('test', $aggregation['extended_stats']['script']);
        $this->assertArrayNotHasKey('field', $aggregation['extended_stats']);
    }

    /**
     * Aggregation assembler used in tests.
     */
    private function getAggregationAssembler(): MetricAssembler
    {
        return new MetricAssembler();
    }
}
