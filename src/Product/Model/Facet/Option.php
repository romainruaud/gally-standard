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

namespace Elasticsuite\Product\Model\Facet;

use ApiPlatform\Core\Action\NotFoundAction;
use ApiPlatform\Core\Annotation\ApiResource;
use Elasticsuite\GraphQl\Decoration\Resolver\Stage\ReadStage;
use Elasticsuite\Product\GraphQl\Type\Definition\FieldFilterInputType;
use Elasticsuite\Search\Model\Facet\Option as FacetOption;
use Elasticsuite\Search\Resolver\DummyResolver;

#[
    ApiResource(
        itemOperations: [
            'get' => [ // Useless api endpoint, but need by api platform in order to return item in the graphql one.
                'controller' => NotFoundAction::class,
                'read' => false,
                'output' => false,
            ],
        ],
        collectionOperations: [],
        paginationEnabled: false,
        graphql: [
            'viewMore' => [
                'collection_query' => DummyResolver::class,
                'read' => true,
                'deserialize' => false,
                'args' => [
                    'localizedCatalog' => ['type' => 'String!', 'description' => 'Localized Catalog'],
                    'aggregation' => ['type' => 'String!', 'description' => 'Source field to get complete aggregation'],
                    'search' => ['type' => 'String', 'description' => 'Query Text'],
                    'currentCategoryId' => ['type' => 'String', 'description' => 'Current category ID'],
                    'filter' => ['type' => '[' . FieldFilterInputType::NAME . ']', ReadStage::IS_GRAPHQL_ELASTICSUITE_ARG_KEY => true],
                ],
            ],
        ],
        shortName: 'ProductFacetOption'
    )
]
class Option extends FacetOption
{
}
