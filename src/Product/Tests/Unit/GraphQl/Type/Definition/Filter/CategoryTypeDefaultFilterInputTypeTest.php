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

namespace Gally\Product\Tests\Unit\GraphQl\Type\Definition\Filter;

use Gally\Entity\GraphQl\Type\Definition\Filter\CategoryTypeDefaultFilterInputType;
use Gally\Search\Constant\FilterOperator;
use Gally\Search\Elasticsearch\Builder\Request\Query\Filter\FilterQueryBuilder;
use Gally\Search\Elasticsearch\Request\ContainerConfigurationInterface;
use Gally\Search\Elasticsearch\Request\QueryFactory;
use GraphQL\Type\Definition\Type;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CategoryTypeDefaultFilterInputTypeTest extends KernelTestCase
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
        $reflector = new \ReflectionClass(CategoryTypeDefaultFilterInputType::class);

        $nestingSeparatorProperty = $reflector->getProperty('nestingSeparator');
        $nameProperty = $reflector->getProperty('name');
        $configProperty = $reflector->getProperty('config');

        $categoryTypeDefaultFilterInputType = new CategoryTypeDefaultFilterInputType(
            self::$filterQueryBuilder,
            self::$queryFactory,
            '##'
        );

        $this->assertEquals(
            [
                'fields' => [
                    FilterOperator::EQ => Type::nonNull(Type::string()),
                ],
            ],
            $categoryTypeDefaultFilterInputType->getConfig()
        );

        $this->assertEquals('##', $nestingSeparatorProperty->getValue($categoryTypeDefaultFilterInputType));

        $this->assertEquals(
            CategoryTypeDefaultFilterInputType::SPECIFIC_NAME,
            $nameProperty->getValue($categoryTypeDefaultFilterInputType)
        );
        $this->assertEquals(
            CategoryTypeDefaultFilterInputType::SPECIFIC_NAME,
            $categoryTypeDefaultFilterInputType->getName()
        );
        $this->assertEquals(
            array_merge(
                ['name' => CategoryTypeDefaultFilterInputType::SPECIFIC_NAME],
                $categoryTypeDefaultFilterInputType->getConfig()
            ),
            $configProperty->getValue($categoryTypeDefaultFilterInputType)
        );
    }

    public function testFieldNames(): void
    {
        $categoryTypeDefaultFilterInputType = new CategoryTypeDefaultFilterInputType(
            self::$filterQueryBuilder,
            self::$queryFactory,
            '##'
        );

        $this->assertEquals('my_category.id', $categoryTypeDefaultFilterInputType->getFilterFieldName('my_category'));
        $this->assertEquals('my_category##id', $categoryTypeDefaultFilterInputType->getGraphQlFieldName('my_category.id'));
        $this->assertEquals('my_category.id', $categoryTypeDefaultFilterInputType->getMappingFieldName('my_category##id'));
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
        $categoryTypeDefaultFilterInputType = new CategoryTypeDefaultFilterInputType(
            self::$filterQueryBuilder,
            self::$queryFactory,
            '__'
        );

        $errors = $categoryTypeDefaultFilterInputType->validate(
            $fieldName,
            $inputData,
            $this->getMockBuilder(ContainerConfigurationInterface::class)->getMock(),
        );
        $this->assertEquals($expectedErrors, $errors);
    }

    public function validateDataProvider(): array
    {
        return [
            ['category__id', ['eq' => 'cat_1'], []],
        ];
    }
}
