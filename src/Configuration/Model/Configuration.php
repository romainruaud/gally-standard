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

namespace Elasticsuite\Configuration\Model;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;

#[ApiResource(
    itemOperations: [],
    collectionOperations: [
        'get' => ['pagination_enabled' => false],
    ],
    graphql: [
        'collection_query' => ['pagination_enabled' => false],
    ],
)]

class Configuration
{
    #[ApiProperty(identifier: true)]
    public string $id;

    public string $value;
}