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

namespace Elasticsuite\Entity\GraphQl\Type\Definition\Filter;

use ApiPlatform\Core\GraphQl\Type\Definition\TypeInterface;
use Elasticsuite\Exception\LogicException;
use Elasticsuite\Metadata\Model\SourceField;
use Elasticsuite\RuleEngine\GraphQl\Type\Definition\RuleFilterInterface;
use Elasticsuite\Search\Constant\FilterOperator;
use Elasticsuite\Search\Elasticsearch\Builder\Request\Query\Filter\FilterQueryBuilder;
use Elasticsuite\Search\Elasticsearch\Request\ContainerConfigurationInterface;
use Elasticsuite\Search\Elasticsearch\Request\QueryFactory;
use Elasticsuite\Search\Elasticsearch\Request\QueryInterface;
use GraphQL\Type\Definition\InputObjectType;

abstract class AbstractFilter extends InputObjectType implements TypeInterface, EntityFilterInterface, RuleFilterInterface
{
    public function __construct(
        protected FilterQueryBuilder $filterQueryBuilder,
        protected QueryFactory $queryFactory,
        protected string $nestingSeparator
    ) {
        parent::__construct($this->getConfig());
    }

    abstract public function getConfig(): array;

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getFilterFieldName(string $sourceFieldCode): string
    {
        return $sourceFieldCode;
    }

    /**
     * {@inheritDoc}
     */
    public function getGraphQlFieldName(string $mappingFilterName): string
    {
        return str_replace('.', $this->nestingSeparator, $mappingFilterName);
    }

    /**
     * {@inheritDoc}
     */
    public function getMappingFieldName(string $graphqlFieldName): string
    {
        return str_replace($this->nestingSeparator, '.', $graphqlFieldName);
    }

    public function transformToElasticsuiteFilter(array $inputFilter, ContainerConfigurationInterface $containerConfig, array $filterContext = []): QueryInterface
    {
        if (isset($inputFilter['exist'])) {
            $existQuery = $this->queryFactory->create(QueryInterface::TYPE_EXISTS, $inputFilter);

            return $inputFilter['exist']
                ? $existQuery
                : $this->queryFactory->create(QueryInterface::TYPE_BOOL, ['mustNot' => [$existQuery]]);
        }

        return $this->filterQueryBuilder->create($containerConfig, $this->getFilterData($inputFilter));
    }

    protected function getFilterData(array $inputFilter): array
    {
        $conditions = [];
        foreach ($this->getConditions() as $condition) {
            if (isset($inputFilter[$condition])) {
                $conditions = array_merge($conditions, [$condition => $inputFilter[$condition]]);
            }
        }

        return [$this->getMappingFieldName($inputFilter['field']) => $conditions];
    }

    protected function getConditions(): array
    {
        return array_keys($this->getConfig()['fields']);
    }

    public function getGraphQlFilterAsArray(SourceField $sourceField, array $fields): array
    {
        return [
            $this->getGraphQlFieldName($this->getFilterFieldName($sourceField->getCode())) => $fields,
        ];
    }

    public function validateValueType(string $field, string $operator, mixed $value): void
    {
        $this->validateValueTypeByType($field, $operator, $value);
    }

    public function validateValueTypeByType(string $field, string $operator, mixed $value, string $type = 'string')
    {
        $typeFunction = $additionalTypeFunction = "is_{$type}";
        if ('float' === $type || 'double' === $type) {
            $additionalTypeFunction = 'is_integer';
        }

        if (FilterOperator::EXIST == $operator) {
            if (!\is_bool($value)) {
                throw new LogicException("For the field '{$field}' the value '{$value}' is not a boolean.");
            }
        } elseif (FilterOperator::IN === $operator || FilterOperator::NOT_IN == $operator) {
            if (!\is_array($value)) {
                throw new LogicException("Expected an array as 'value' for rule on field '{$field}' and operator '{$operator}', received '{$value}'.");
            }
            foreach ($value as $item) {
                if (!$typeFunction($item) && !$additionalTypeFunction($item)) {
                    throw new LogicException("Expected an array of '{$type}' for rule on field '{$field}' and operator '{$operator}', got: " . print_r($value, true));
                }
            }
        } else {
            if (!$typeFunction($value) && !$additionalTypeFunction($value)) {
                throw new LogicException("For the field '{$field}' the value '" . print_r($value, true) . "' is not of type '{$type}'.");
            }
        }
    }
}
