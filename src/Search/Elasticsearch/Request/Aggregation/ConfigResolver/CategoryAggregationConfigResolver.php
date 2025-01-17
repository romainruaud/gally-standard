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

namespace Elasticsuite\Search\Elasticsearch\Request\Aggregation\ConfigResolver;

use Elasticsuite\Category\Repository\CategoryRepository;
use Elasticsuite\Category\Service\CurrentCategoryProvider;
use Elasticsuite\Metadata\Model\SourceField;
use Elasticsuite\Search\Elasticsearch\Request\BucketInterface;
use Elasticsuite\Search\Elasticsearch\Request\ContainerConfigurationInterface;
use Elasticsuite\Search\Elasticsearch\Request\QueryFactory;
use Elasticsuite\Search\Elasticsearch\Request\QueryInterface;
use Elasticsuite\Search\Model\Facet\Configuration;

class CategoryAggregationConfigResolver implements FieldAggregationConfigResolverInterface
{
    public function __construct(
        private CurrentCategoryProvider $currentCategoryProvider,
        private CategoryRepository $categoryRepository,
        private QueryFactory $queryFactory,
    ) {
    }

    public function supports(Configuration $facetConfig): bool
    {
        return SourceField\Type::TYPE_CATEGORY === $facetConfig->getSourceField()->getType();
    }

    /**
     * The category aggregation should return only the categories that are direct child of the current category.
     * If no category are provided in context, the aggregation should return only first level categories.
     */
    public function getConfig(ContainerConfigurationInterface $containerConfig, Configuration $facetConfig): array
    {
        $config = [];

        $currentCategory = $this->currentCategoryProvider->getCurrentCategory();
        $children = $this->categoryRepository->findBy(['parentId' => $currentCategory]);
        $queries = [];

        foreach ($children as $child) {
            $queries[$child->getId()] = $this->queryFactory->create(
                QueryInterface::TYPE_TERM,
                ['field' => $facetConfig->getSourceField()->getCode() . '.id', 'value' => $child->getId()]
            );
        }

        if (!empty($queries)) {
            $config = [
                'name' => $facetConfig->getSourceField()->getCode() . '.id',
                'type' => BucketInterface::TYPE_QUERY_GROUP,
                'queries' => $queries,
            ];
        }

        return $config;
    }
}
