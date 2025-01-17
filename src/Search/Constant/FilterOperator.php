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

namespace Elasticsuite\Search\Constant;

final class FilterOperator
{
    public const EQ = 'eq';
    public const NOT_EQ = self::NOT_PREFIX . 'eq';
    public const GTE = 'gte';
    public const LTE = 'lte';
    public const GT = 'gt';
    public const LT = 'lt';
    public const MATCH = 'match';
    public const NOT_MATCH = self::NOT_PREFIX . 'match';
    public const IN = 'in';
    public const NOT_IN = self::NOT_PREFIX . 'in';
    public const EXIST = 'exist';

    public const NOT_PREFIX = '!';
}
