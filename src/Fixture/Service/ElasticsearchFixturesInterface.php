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

namespace Elasticsuite\Fixture\Service;

interface ElasticsearchFixturesInterface
{
    public const PREFIX_TEST_INDEX = 'elasticsuite_test__';

    public function loadFixturesIndexFiles(array $pathFiles): void;

    public function loadFixturesDocumentFiles(array $pathFiles): void;

    public function deleteTestFixtures(): void;
}
