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

namespace Elasticsuite\Search\GraphQl\Type\Definition\Filter;

use Elasticsuite\Metadata\Model\SourceField;
use Elasticsuite\Search\Elasticsearch\Request\ContainerConfigurationInterface;
use Elasticsuite\Search\Service\ReverseSourceFieldProvider;

trait FilterableFieldTrait
{
    /**
     * @var array<string, boolean>
     */
    private array $sourceFieldsValidated;

    private ReverseSourceFieldProvider $reverseSourceFieldProvider;

    public function validateIsFilterable(string $fieldName, ContainerConfigurationInterface $containerConfig): array
    {
        if (isset($this->sourceFieldsValidated[$fieldName])) {
            return [];
        }

        $errors = [];
        $sourceField = $this->reverseSourceFieldProvider->getSourceFieldFromFieldName($fieldName, $containerConfig->getMetadata());

        /*
         * For the MVP we check only if the source field exists.
         * We can't check if the source field is_filterable or is_used_for_rules,
         * because the sku would not filterable via the API for example.
         */
        if (!$sourceField instanceof SourceField) {
            $errors[] = "The field '{$fieldName}' does not exist";
        }

        $this->sourceFieldsValidated[$fieldName] = true;

        return $errors;
    }
}
