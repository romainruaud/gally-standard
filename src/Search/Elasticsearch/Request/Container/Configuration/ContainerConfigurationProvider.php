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

namespace Elasticsuite\Search\Elasticsearch\Request\Container\Configuration;

use Elasticsuite\Catalog\Model\LocalizedCatalog;
use Elasticsuite\Metadata\Model\Metadata;
use Elasticsuite\Search\Elasticsearch\Request\ContainerConfigurationFactoryInterface;
use Elasticsuite\Search\Elasticsearch\Request\ContainerConfigurationInterface;

class ContainerConfigurationProvider
{
    /** @var ContainerConfigurationFactoryInterface[][] */
    private array $containerConfigFactories;

    public function __construct(private GenericContainerConfigurationFactory $genericConfigurationFactory)
    {
    }

    /**
     * Add factories via compiler pass.
     *
     * @see \Elasticsuite\Search\Compiler\GetContainerConfigurationFactory
     */
    public function addContainerConfigFactory(
        string $requestType,
        ContainerConfigurationFactoryInterface $configurationFactory,
        string $entityCode = 'generic'
    ): void {
        $this->containerConfigFactories[$entityCode][$requestType] = $configurationFactory;
    }

    /**
     * Create a container configuration based on provided entity metadata and catalog ID.
     *
     * @param Metadata         $metadata         Search request target entity metadata
     * @param LocalizedCatalog $localizedCatalog Search request target catalog
     * @param string|null      $requestType      Search request type
     *
     * @throws \LogicException Thrown when the search container is not found into the configuration
     */
    public function get(Metadata $metadata, LocalizedCatalog $localizedCatalog, ?string $requestType = null): ContainerConfigurationInterface
    {
        if (null === $requestType) {
            $factory = $this->genericConfigurationFactory;
        } else {
            $entityCode = $metadata->getEntity();
            $entityCode = \array_key_exists($entityCode, $this->containerConfigFactories) ? $entityCode : 'generic';

            if (!\array_key_exists($requestType, $this->containerConfigFactories[$entityCode])) {
                throw new \LogicException(sprintf('The request type %s is not defined.', $requestType));
            }

            $factory = $this->containerConfigFactories[$entityCode][$requestType];
        }

        return $factory->create($requestType ?? 'generic', $metadata, $localizedCatalog);
    }

    /**
     * Get all available request type names.
     *
     * @return string[]
     */
    public function getAvailableRequestType(string $entityCode = 'generic'): array
    {
        return array_keys($this->containerConfigFactories[$entityCode]);
    }
}
