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

namespace Elasticsuite\Product\GraphQl\Type\Definition\SortOrder;

use Elasticsuite\Metadata\Model\SourceField;

interface SortOrderProviderInterface
{
    /**
     * Returns true if the sort order provider supports the provided source field.
     *
     * @param SourceField $sourceField Source field
     */
    public function supports(SourceField $sourceField): bool;

    /**
     * Get the sort order field name corresponding to the provided source field.
     *
     * @param SourceField $sourceField Source field
     */
    public function getSortOrderField(SourceField $sourceField): string;

    /**
     * Get the sort order detailed label corresponding to the provided source field.
     *
     * @param SourceField $sourceField Source field
     */
    public function getLabel(SourceField $sourceField): string;

    /**
     * Get the sort order simplified label corresponding to the provided source field.
     *
     * @param SourceField $sourceField Source field
     */
    public function getSimplifiedLabel(SourceField $sourceField): string;
}
