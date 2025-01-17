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
 * Structured source field/entity attribute stitching description interface.
 */
interface StructuredAttributeInterface
{
    /**
     * Get the inner fields of a structured source field which will act as inner attributes.
     *
     * @return array An array whose keys are the inner field names and the values their corresponding stitching class
     */
    public static function getFields(): array;

    /**
     * Returns true if the structure source field is supposed to hold and render multiple values in the API.
     * For instance, multi-select source fields are supposed to both hold and render multiple values,
     * while for prices, at query time, a single context dependant structure could be returned.
     */
    public static function isList(): bool;
}
