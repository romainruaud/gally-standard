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

namespace Elasticsuite\Search\Elasticsearch\Builder\Request\Query\Fulltext;

use Elasticsuite\Index\Model\Index\Mapping\FieldFilterInterface;
use Elasticsuite\Index\Model\Index\Mapping\FieldInterface;

/**
 * Indicates if a field is used in fulltext search.
 */
class SearchableFieldFilter implements FieldFilterInterface
{
    /**
     * {@inheritDoc}
     */
    public function filterField(FieldInterface $field): bool
    {
        return FieldInterface::FIELD_TYPE_TEXT == $field->getType()
            && $field->isSearchable()
            && false === $field->isNested();
    }
}
