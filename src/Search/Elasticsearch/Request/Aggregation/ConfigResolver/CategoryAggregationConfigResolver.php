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

use Elasticsuite\Metadata\Model\SourceField;
use Elasticsuite\Search\Elasticsearch\Request\BucketInterface;

class CategoryAggregationConfigResolver implements FieldAggregationConfigResolverInterface
{
    public function supports(SourceField $sourceField): bool
    {
        return SourceField\Type::TYPE_CATEGORY === $sourceField->getType();
    }

    public function getConfig(SourceField $sourceField): array
    {
        return [
            'name' => $sourceField->getCode(),
            'type' => BucketInterface::TYPE_MULTI_TERMS,
            'field' => $sourceField->getCode() . '.id',
            'additionalFields' => [
                $sourceField->getCode() . '.name',
            ],
        ];
//        return [
//            'name' => $sourceField->getCode(),
//            'field' => $sourceField->getCode() . '.id',
//            'type' => BucketInterface::TYPE_TERMS,
//            'childAggregations' => [
//                [
//                    'name' => $sourceField->getCode() . '.label',
//                    'field' => $sourceField->getCode() . '.name',
//                    'type' => BucketInterface::TYPE_TERMS,
//                    'unsetNestedPath' => true,
//                ],
//            ],
//        ];
    }
}