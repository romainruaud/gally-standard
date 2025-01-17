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

namespace Elasticsuite\Entity\Model\Attribute;

/**
 * GraphQL schema dynamic attributes stitching interface for scalar attributes.
 */
interface GraphQlAttributeInterface
{
    /**
     * Return the GraphQL type to use to represent the scalar attribute.
     */
    public static function getGraphQlType(): mixed;
}
