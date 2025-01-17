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

namespace Elasticsuite\Category\Tests\Api\GraphQl;

use Elasticsuite\Catalog\Repository\CatalogRepository;
use Elasticsuite\Catalog\Repository\LocalizedCatalogRepository;
use Elasticsuite\Test\AbstractTest;
use Elasticsuite\Test\ExpectedResponse;
use Elasticsuite\Test\RequestGraphQlToTest;
use Elasticsuite\User\Constant\Role;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CategoryTreeTest extends AbstractTest
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::loadFixture([
            __DIR__ . '/../../fixtures/metadata.yaml',
            __DIR__ . '/../../fixtures/catalogs.yaml',
            __DIR__ . '/../../fixtures/categories.yaml',
            __DIR__ . '/../../fixtures/configurations.yaml',
        ]);
    }

    public function testInvalidCatalog(): void
    {
        $this->assertSame(
            'Catalog with id 999 not found.',
            $this->getCategoryTree('b2c_it')[0]['debugMessage'],
        );
    }

    public function testGetCategoryTree(): void
    {
        $this->assertSame(
            [
                [
                    'id' => 'one',
                    'name' => 'Un',
                    'level' => 1,
                    'path' => 'one',
                    'isVirtual' => true,
                    'children' => [
                        [
                            'id' => 'three',
                            'name' => 'Trois',
                            'level' => 2,
                            'path' => 'one/three',
                            'isVirtual' => false,
                        ],
                    ],
                ],
            ],
            $this->getCategoryTree('b2c', 'b2c_fr')
        );

        $this->assertSame(
            [
                [
                    'id' => 'one',
                    'name' => 'One',
                    'level' => 1,
                    'path' => 'one',
                    'isVirtual' => false,
                    'children' => [
                        [
                            'id' => 'three',
                            'name' => 'Three',
                            'level' => 2,
                            'path' => 'one/three',
                            'isVirtual' => false,
                        ],
                    ],
                ],
                [
                    'id' => 'two',
                    'name' => 'Two',
                    'level' => 1,
                    'path' => 'two',
                    'isVirtual' => false,
                ],
            ],
            $this->getCategoryTree('b2c', 'b2c_en')
        );

        $this->assertSame(
            [
                [
                    'id' => 'five',
                    'name' => 'Five',
                    'level' => 1,
                    'path' => 'five',
                    'isVirtual' => false,
                ],
                [
                    'id' => 'one',
                    'name' => 'One',
                    'level' => 1,
                    'path' => 'one',
                    'isVirtual' => true,
                ],
            ],
            $this->getCategoryTree('b2b')
        );

        $this->assertSame(
            [
                [
                    'id' => 'five',
                    'name' => 'Five',
                    'level' => 1,
                    'path' => 'five',
                    'isVirtual' => false,
                ],
                [
                    'id' => 'one',
                    'name' => 'Un',
                    'level' => 1,
                    'path' => 'one',
                    'isVirtual' => true,
                    'children' => [
                        [
                            'id' => 'three',
                            'name' => 'Trois',
                            'level' => 2,
                            'path' => 'one/three',
                            'isVirtual' => false,
                        ],
                    ],
                ],
                [
                    'id' => 'two',
                    'name' => 'Deux',
                    'level' => 1,
                    'path' => 'two',
                    'isVirtual' => false,
                ],
            ],
            $this->getCategoryTree()
        );
    }

    protected function getCategoryTree(?string $catalogCode = null, ?string $localizedCatalogCode = null): array
    {
        $responseData = [];
        $localizedCatalogRepository = static::getContainer()->get(LocalizedCatalogRepository::class);
        $catalogRepository = static::getContainer()->get(CatalogRepository::class);

        $params = [];
        if ($catalogCode) {
            $catalog = $catalogRepository->findOneBy(['code' => $catalogCode]);
            $params[] = 'catalogId:' . ($catalog ? $catalog->getId() : '999');
        }
        if ($localizedCatalogCode) {
            $localizedCatalog = $localizedCatalogRepository->findOneBy(['code' => $localizedCatalogCode]);
            $params[] = 'localizedCatalogId:' . ($localizedCatalog ? $localizedCatalog->getId() : '999');
        }

        $query = !empty($params) ? '(' . implode(',', $params) . ')' : '';

        $this->validateApiCall(
            new RequestGraphQlToTest(
                <<<GQL
                    {
                      getCategoryTree $query {
                        categories
                      }
                    }
                GQL,
                $this->getUser(Role::ROLE_ADMIN),
            ),
            new ExpectedResponse(
                200,
                function (ResponseInterface $response) use (&$responseData) {
                    $response = $response->toArray();
                    if (\array_key_exists('errors', $response)) {
                        $responseData = $response['errors'];
                    } else {
                        $responseData = $response['data']['getCategoryTree']['categories'];
                    }
                }
            )
        );

        return $responseData;
    }
}
