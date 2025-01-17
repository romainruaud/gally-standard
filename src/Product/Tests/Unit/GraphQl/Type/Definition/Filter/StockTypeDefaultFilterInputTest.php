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

namespace Elasticsuite\Product\Tests\Unit\GraphQl\Type\Definition\Filter;

use Elasticsuite\Entity\GraphQl\Type\Definition\Filter\StockTypeDefaultFilterInputType;
use Elasticsuite\Search\Constant\FilterOperator;
use Elasticsuite\Search\Elasticsearch\Builder\Request\Query\Filter\FilterQueryBuilder;
use Elasticsuite\Search\Elasticsearch\Request\ContainerConfigurationInterface;
use Elasticsuite\Search\Elasticsearch\Request\QueryFactory;
use GraphQL\Type\Definition\Type;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StockTypeDefaultFilterInputTest extends KernelTestCase
{
    private static FilterQueryBuilder $filterQueryBuilder;

    private static QueryFactory $queryFactory;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$filterQueryBuilder = static::getContainer()->get(FilterQueryBuilder::class);
        self::$queryFactory = static::getContainer()->get(QueryFactory::class);
    }

    public function testInstantiate(): void
    {
        $reflector = new \ReflectionClass(StockTypeDefaultFilterInputType::class);

        $nestingSeparatorProperty = $reflector->getProperty('nestingSeparator');
        $nameProperty = $reflector->getProperty('name');
        $configProperty = $reflector->getProperty('config');

        $stockTypeDefaultFilterInputType = new StockTypeDefaultFilterInputType(
            self::$filterQueryBuilder,
            self::$queryFactory,
            '##'
        );

        $this->assertEquals(
            [
                'fields' => [
                    FilterOperator::EQ => Type::boolean(),
                    FilterOperator::EXIST => Type::boolean(),
                ],
            ],
            $stockTypeDefaultFilterInputType->getConfig()
        );

        $this->assertEquals('##', $nestingSeparatorProperty->getValue($stockTypeDefaultFilterInputType));

        $this->assertEquals(
            StockTypeDefaultFilterInputType::SPECIFIC_NAME,
            $nameProperty->getValue($stockTypeDefaultFilterInputType)
        );
        $this->assertEquals(
            StockTypeDefaultFilterInputType::SPECIFIC_NAME,
            $stockTypeDefaultFilterInputType->getName()
        );
        $this->assertEquals(
            array_merge(
                ['name' => StockTypeDefaultFilterInputType::SPECIFIC_NAME],
                $stockTypeDefaultFilterInputType->getConfig()
            ),
            $configProperty->getValue($stockTypeDefaultFilterInputType)
        );
    }

    public function testFieldNames(): void
    {
        $stockTypeDefaultFilterInputType = new StockTypeDefaultFilterInputType(
            self::$filterQueryBuilder,
            self::$queryFactory,
            '##'
        );

        $this->assertEquals('my_stock.status', $stockTypeDefaultFilterInputType->getFilterFieldName('my_stock'));
        $this->assertEquals('my_stock##status', $stockTypeDefaultFilterInputType->getGraphQlFieldName('my_stock.status'));
        $this->assertEquals('my_stock.status', $stockTypeDefaultFilterInputType->getMappingFieldName('my_stock##status'));
    }

    /**
     * @dataProvider validateDataProvider
     *
     * @param string $fieldName      Field name
     * @param array  $inputData      Input data
     * @param array  $expectedErrors Array of expected error messages (empty if no errors expected)
     */
    public function testValidate(string $fieldName, array $inputData, array $expectedErrors): void
    {
        $stockTypeDefaultFilterInputType = new StockTypeDefaultFilterInputType(
            self::$filterQueryBuilder,
            self::$queryFactory,
            '__'
        );

        $errors = $stockTypeDefaultFilterInputType->validate(
            $fieldName,
            $inputData,
            $this->getMockBuilder(ContainerConfigurationInterface::class)->getMock(),
        );
        $this->assertEquals($expectedErrors, $errors);
    }

    public function validateDataProvider(): array
    {
        return [
            ['stock__status', ['eq' => true], []],
            ['stock__status', ['exist' => true], []],
            [
                'stock__status',
                ['eq' => true, 'exist' => true],
                ["Filter argument stock__status: Only 'eq' or 'exist' should be filled."],
            ],
            [
                'stock__status',
                [],
                ["Filter argument stock__status: At least 'eq' or 'exist' should be filled."],
            ],
        ];
    }
}
