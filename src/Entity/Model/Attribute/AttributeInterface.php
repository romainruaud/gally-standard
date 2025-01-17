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
 * Attribute hydration interface for dynamic attributes.
 */
interface AttributeInterface
{
    /**
     * Get the attribute identifier under which the attribute value will be stored in the entity dynamic attributes.
     */
    public function getAttributeCode(): string;

    /**
     * Get the attribute value to store when hydrating the entity.
     */
    public function getValue(): mixed;
}
