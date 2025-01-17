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

namespace Elasticsuite\Search\Elasticsearch\Request\Aggregation\Modifier;

use Elasticsuite\Search\Elasticsearch\Builder\Request\SimpleRequestBuilder;
use Elasticsuite\Search\Elasticsearch\Request\Aggregation\CoverageProvider;
use Elasticsuite\Search\Elasticsearch\Request\Container\Configuration\ContainerConfigurationProvider;
use Elasticsuite\Search\Elasticsearch\Request\ContainerConfigurationInterface;
use Elasticsuite\Search\Elasticsearch\Request\QueryInterface;
use Elasticsuite\Search\Model\Facet\Configuration as FacetConfiguration;

/**
 * Coverage Modifier for filterable product source field provider.
 */
class Coverage implements ModifierInterface
{
    public function __construct(
        private SimpleRequestBuilder $coverageRequestBuilder,
        private CoverageProvider $coverageProvider,
        private ContainerConfigurationProvider $containerConfigurationProvider,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function modifyFacetConfigs(
        ContainerConfigurationInterface $containerConfig,
        array $facetConfigs,
        QueryInterface|string|null $query,
        array $filters,
        array $queryFilters
    ): array {
        $relevantFacets = [];
        $coverageRates = $this->getCoverageRates($containerConfig, $query, $filters, $queryFilters);

        foreach ($facetConfigs as $facetConfig) {
            $sourceFieldCode = $facetConfig->getSourceField()->getCode();
            $minCoverageRate = $facetConfig->getCoverageRate();

            $isRelevant = isset($coverageRates[$sourceFieldCode]) && ($coverageRates[$sourceFieldCode] >= $minCoverageRate);
            $forceDisplay = FacetConfiguration::DISPLAY_MODE_ALWAYS_DISPLAYED == $facetConfig->getDisplayMode();
            $isHidden = FacetConfiguration::DISPLAY_MODE_ALWAYS_HIDDEN == $facetConfig->getDisplayMode();

            if (!($isHidden || !($isRelevant || $forceDisplay))) {
                $relevantFacets[] = $facetConfig;
            }
        }

        return $relevantFacets;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyAggregations(
        ContainerConfigurationInterface $containerConfig,
        array $aggregations,
        QueryInterface|string|null $query,
        array $filters,
        array $queryFilters
    ): array {
        return $aggregations;
    }

    /**
     * Get coverage rate of attributes for current search request.
     *
     * @param ContainerConfigurationInterface $containerConfig search container configuration
     * @param QueryInterface|string|null      $query           Current Query
     * @param array                           $filters         search request filters
     * @param QueryInterface[]                $queryFilters    search request filters prebuilt as QueryInterface
     */
    private function getCoverageRates(
        ContainerConfigurationInterface $containerConfig,
        QueryInterface|string|null $query = null,
        array $filters = [],
        array $queryFilters = []
    ): array {
        $coverageConfigProvider = $this->containerConfigurationProvider->get(
            $containerConfig->getMetadata(),
            $containerConfig->getLocalizedCatalog(),
            'coverage_rate'
        );

        $coverageRequest = $this->coverageRequestBuilder->create(
            $coverageConfigProvider,
            0,
            0,
            $query,
            [],
            $filters,
            $queryFilters
        );

        $coverage = $this->coverageProvider->getCoverage($coverageRequest);
        $coverageRates = [];
        $totalCount = $coverage->getSize() ?: 1;

        foreach ($coverage->getProductCountByAttributeCode() as $attributeCode => $productCount) {
            $coverageRates[$attributeCode] = $productCount / $totalCount * 100;
        }

        return $coverageRates;
    }
}
