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

namespace Gally\Search\Elasticsearch\Adapter\Common\Request\Query\Assembler;

use Gally\Search\Elasticsearch\Adapter\Common\Request\Query\AssemblerInterface;
use Gally\Search\Elasticsearch\Request\QueryInterface;

/**
 * Assemble an ES filtered query.
 */
class Filtered extends AbstractComplexAssembler implements AssemblerInterface
{
    /**
     * {@inheritDoc}
     */
    public function assembleQuery(QueryInterface $query): array
    {
        if (QueryInterface::TYPE_FILTER !== $query->getType()) {
            throw new \InvalidArgumentException("Query assembler : invalid query type {$query->getType()}");
        }

        /** @var \Gally\Search\Elasticsearch\Request\Query\Filtered $query */
        $searchQuery = [];

        if ($query->getFilter()) {
            $searchQuery['filter'] = $this->parentAssembler->assembleQuery($query->getFilter());
        }

        if ($query->getQuery()) {
            $searchQuery['must'] = $this->parentAssembler->assembleQuery($query->getQuery());
        }

        if ($query->getName()) {
            $searchQuery['_name'] = $query->getName();
        }

        $queryType = isset($searchQuery['must']) ? 'bool' : 'constant_score';

        if ('constant_score' === $queryType && !isset($searchQuery['filter'])) {
            $searchQuery['filter'] = ['match_all' => new \stdClass()];
        }

        $searchQuery['boost'] = $query->getBoost();

        return [$queryType => $searchQuery];
    }
}
