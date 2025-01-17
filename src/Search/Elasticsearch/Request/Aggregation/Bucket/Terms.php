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

namespace Elasticsuite\Search\Elasticsearch\Request\Aggregation\Bucket;

use Elasticsuite\Search\Elasticsearch\Request\AggregationInterface;
use Elasticsuite\Search\Elasticsearch\Request\BucketInterface;
use Elasticsuite\Search\Elasticsearch\Request\QueryInterface;

/**
 * Terms Bucket implementation.
 */
class Terms extends AbstractBucket
{
    /**
     * Constructor.
     *
     * @param string                 $name              Bucket name
     * @param string                 $field             Bucket field
     * @param AggregationInterface[] $childAggregations Child aggregations
     * @param ?string                $nestedPath        Nested path for nested bucket
     * @param ?QueryInterface        $filter            Bucket filter
     * @param ?QueryInterface        $nestedFilter      Nested filter for the bucket
     * @param int                    $size              Bucket size
     * @param string|string[]        $sortOrder         Bucket sort order
     * @param string[]               $include           Include bucket filter
     * @param string[]               $exclude           Exclude bucket filter
     * @param ?int                   $minDocCount       Min doc count bucket filter
     */
    public function __construct(
        string $name,
        string $field,
        array $childAggregations = [],
        ?string $nestedPath = null,
        ?QueryInterface $filter = null,
        ?QueryInterface $nestedFilter = null,
        private int $size = 0,
        private string|array $sortOrder = BucketInterface::SORT_ORDER_COUNT,
        private array $include = [],
        private array $exclude = [],
        private ?int $minDocCount = null
    ) {
        parent::__construct($name, $field, $childAggregations, $nestedPath, $filter, $nestedFilter);

        $this->size = $size > 0 && $size < self::MAX_BUCKET_SIZE ? $size : self::MAX_BUCKET_SIZE;
    }

    /**
     * {@inheritDoc}
     */
    public function getType(): string
    {
        return BucketInterface::TYPE_TERMS;
    }

    /**
     * Bucket size.
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Bucket sort order.
     */
    public function getSortOrder(): string|array
    {
        return $this->sortOrder;
    }

    /**
     * Bucket include filter.
     *
     * @return string[]
     */
    public function getInclude(): array
    {
        return $this->include;
    }

    /**
     * Bucket exclude filter.
     *
     * @return string[]
     */
    public function getExclude(): array
    {
        return $this->exclude;
    }

    /**
     * Bucket min doc count filter.
     */
    public function getMinDocCount(): ?int
    {
        return $this->minDocCount;
    }
}
