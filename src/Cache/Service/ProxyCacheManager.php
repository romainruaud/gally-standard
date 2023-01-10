<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Gally to newer versions in the future.
 *
 * @package   Gally
 * @author    Gally Team <elasticsuite@smile.fr>
 * @copyright 2022-present Smile
 * @license   Open Software License v. 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Gally\Cache\Service;

use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Gally\ResourceMetadata\Service\ResourceMetadataManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ProxyCacheManager
{
    public function __construct(
        private RequestStack $requestStack,
        private ResourceMetadataManager $resourceMetadataManager,
        private ResourceMetadataFactoryInterface $resourceMetadataFactory,
        private IriConverterInterface $iriConverter,
    ) {
    }

    /**
     * Add $resources in attribute '_resources' request.
     * The attribute resource is used to generate Cache-tags header @see \ApiPlatform\Core\HttpCache\EventListener\AddTagsListener::onKernelResponse.
     */
    public function addResourcesToRequest(array $resources, ?Request $request): void
    {
        if (null === $request) {
            $request = $this->requestStack->getCurrentRequest();
        }

        $request->attributes->set('_resources', $request->attributes->get('_resources', []) + $resources);
    }

    /**
     * Allows to add cache tags related to the resource classes found in the ApiResource metadata node gally/cache_tag/resource_classes in $resourceClass.
     */
    public function addCacheTagResourceCollection(string $resourceClass, ?Request $request = null): void
    {
        $resourceMetadata = $this->resourceMetadataFactory->create($resourceClass);
        $resourceClasses = $this->resourceMetadataManager->getCacheTagResourceClasses($resourceMetadata);

        if (null !== $resourceClasses) {
            $resources = [];
            foreach ($resourceClasses as $class) {
                $iri = $this->iriConverter->getIriFromResourceClass($class);
                $resources[$iri] = $iri;
            }

            $this->addResourcesToRequest($resources, $request);
        }
    }
}
