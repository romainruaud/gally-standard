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
/**
 * DISCLAIMER.
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @author    ElasticSuite Team <elasticsuite@smile.fr>
 * @copyright {2022} Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Elasticsuite\Index\Service;

use Elasticsuite\Catalog\Model\LocalizedCatalog;

interface IndexSettingsInterface
{
    /**
     * Returns the index alias for an identifier (eg. catalog_product) by store.
     *
     * @param string                      $indexIdentifier Index identifier
     * @param int|string|LocalizedCatalog $catalog         The catalog
     */
    public function getIndexAliasFromIdentifier(string $indexIdentifier, LocalizedCatalog|int|string $catalog): string;

    /**
     * Create a new index for an identifier (eg. catalog_product) by store including current date.
     *
     * @param string                      $indexIdentifier Index identifier
     * @param int|string|LocalizedCatalog $catalog         The catalog
     */
    public function createIndexNameFromIdentifier(string $indexIdentifier, LocalizedCatalog|int|string $catalog): string;

    /**
     * Load analysis settings by catalog.
     *
     * @param int|string|LocalizedCatalog $catalog The catalog
     *
     * @return array<mixed>
     */
    public function getAnalysisSettings(LocalizedCatalog|int|string $catalog): array;

    /**
     * Returns settings used during index creation.
     *
     * @return array<mixed>
     */
    public function getCreateIndexSettings(): array;

    /**
     * Returns settings used when installing an index.
     *
     * @return array<mixed>
     */
    public function getInstallIndexSettings(): array;

    /**
     * Returns the list of the available indices declared in elasticsuite_indices.xml.
     *
     * @return array<mixed>
     */
    public function getIndicesConfig(): array;

    /**
     * Return config of an index.
     *
     * @param string $indexIdentifier index identifier
     *
     * @return array<mixed>
     */
    public function getIndexConfig(string $indexIdentifier): array;

    /**
     * Get indexing batch size configured.
     */
    public function getBatchIndexingSize(): int;

    /**
     * Get dynamic index settings per store (language).
     *
     * @param int|string|LocalizedCatalog $catalog Catalog
     *
     * @return array<mixed>
     */
    public function getDynamicIndexSettings(LocalizedCatalog|int|string $catalog): array;
}
