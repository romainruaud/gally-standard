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

namespace Elasticsuite\Product\Tests\Api\GraphQl;

use Elasticsuite\Fixture\Service\ElasticsearchFixturesInterface;
use Elasticsuite\Search\Elasticsearch\Request\SortOrderInterface;
use Elasticsuite\Test\AbstractTest;
use Elasticsuite\Test\ExpectedResponse;
use Elasticsuite\Test\RequestGraphQlToTest;
use Elasticsuite\User\Constant\Role;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SearchProductsTest extends AbstractTest
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::loadFixture([
            __DIR__ . '/../../fixtures/catalogs.yaml',
            __DIR__ . '/../../fixtures/metadata.yaml',
            __DIR__ . '/../../fixtures/source_field.yaml',
        ]);
        self::createEntityElasticsearchIndices('product');
        self::loadElasticsearchDocumentFixtures([__DIR__ . '/../../fixtures/product_documents.json']);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        self::deleteEntityElasticsearchIndices('product');
    }

    /**
     * @dataProvider basicSearchProductsDataProvider
     *
     * @param string  $catalogId            Catalog ID or code
     * @param ?int    $pageSize             Pagination size
     * @param ?int    $currentPage          Current page
     * @param ?array  $expectedError        Expected error
     * @param ?int    $expectedItemsCount   Expected items count in (paged) response
     * @param ?int    $expectedTotalCount   Expected total items count
     * @param ?int    $expectedItemsPerPage Expected pagination items per page
     * @param ?int    $expectedLastPage     Expected number of the last page
     * @param ?string $expectedIndexAlias   Expected index alias
     * @param ?float  $expectedScore        Expected score
     */
    public function testBasicSearchProducts(
        string $catalogId,
        ?int $pageSize,
        ?int $currentPage,
        ?array $expectedError,
        ?int $expectedItemsCount,
        ?int $expectedTotalCount,
        ?int $expectedItemsPerPage,
        ?int $expectedLastPage,
        ?string $expectedIndexAlias,
        ?float $expectedScore
    ): void {
        $user = $this->getUser(Role::ROLE_CONTRIBUTOR);

        $arguments = sprintf(
            'catalogId: "%s"',
            $catalogId
        );
        if (null !== $pageSize) {
            $arguments .= sprintf(', pageSize: %d', $pageSize);
        }
        if (null !== $currentPage) {
            $arguments .= sprintf(', currentPage: %d', $currentPage);
        }

        $this->validateApiCall(
            new RequestGraphQlToTest(
                <<<GQL
                    {
                        searchProducts({$arguments}) {
                            collection {
                              id
                              score
                              index
                            }
                            paginationInfo {
                              itemsPerPage
                              lastPage
                              totalCount
                            }
                        }
                    }
                GQL,
                $user
            ),
            new ExpectedResponse(
                200,
                function (ResponseInterface $response) use (
                    $expectedError,
                    $expectedItemsCount,
                    $expectedTotalCount,
                    $expectedItemsPerPage,
                    $expectedLastPage,
                    $expectedIndexAlias,
                    $expectedScore
                ) {
                    if (!empty($expectedError)) {
                        $this->assertJsonContains($expectedError);
                        $this->assertJsonContains([
                            'data' => [
                                'searchProducts' => null,
                            ],
                        ]);
                    } else {
                        $this->assertJsonContains([
                            'data' => [
                                'searchProducts' => [
                                    'paginationInfo' => [
                                        'itemsPerPage' => $expectedItemsPerPage,
                                        'lastPage' => $expectedLastPage,
                                        'totalCount' => $expectedTotalCount,
                                    ],
                                ],
                            ],
                        ]);

                        $responseData = $response->toArray();
                        $this->assertIsArray($responseData['data']['searchProducts']['collection']);
                        $this->assertCount($expectedItemsCount, $responseData['data']['searchProducts']['collection']);
                        foreach ($responseData['data']['searchProducts']['collection'] as $document) {
                            $this->assertArrayHasKey('score', $document);
                            $this->assertEquals($expectedScore, $document['score']);

                            $this->assertArrayHasKey('index', $document);
                            $this->assertStringStartsWith($expectedIndexAlias, $document['index']);
                        }
                    }
                }
            )
        );
    }

    public function basicSearchProductsDataProvider(): array
    {
        return [
            [
                'b2c_uk',   // catalog ID.
                null,   // page size.
                null,   // current page.
                ['errors' => [['message' => 'Internal server error', 'debugMessage' => 'Missing catalog [b2c_uk]']]], // expected error.
                null,   // expected items count.
                null,   // expected total count.
                null,   // expected items per page.
                null,   // expected last page.
                null,   // expected index.
                null,   // expected score.
            ],
            [
                '2',    // catalog ID.
                10,     // page size.
                1,      // current page.
                [],     // expected error.
                10,     // expected items count.
                14,     // expected total count.
                10,     // expected items per page.
                2,      // expected last page.
                ElasticsearchFixturesInterface::PREFIX_TEST_INDEX . 'elasticsuite_b2c_en_product', // expected index alias.
                1.0,    // expected score.
            ],
            [
                'b2c_en',   // catalog ID.
                10,     // page size.
                1,      // current page.
                [],     // expected error.
                10,     // expected items count.
                14,     // expected total count.
                10,     // expected items per page.
                2,      // expected last page.
                ElasticsearchFixturesInterface::PREFIX_TEST_INDEX . 'elasticsuite_b2c_en_product', // expected index alias.
                1.0,    // expected score.
            ],
            [
                'b2c_en',   // catalog ID.
                10,     // page size.
                2,      // current page.
                [],     // expected error.
                4,      // expected items count.
                14,     // expected total count.
                10,     // expected items per page.
                2,      // expected last page.
                ElasticsearchFixturesInterface::PREFIX_TEST_INDEX . 'elasticsuite_b2c_en_product', // expected index alias.
                1.0,    // expected score.
            ],
            [
                'b2c_fr',   // catalog ID.
                null,   // page size.
                null,   // current page.
                [],     // expected error.
                12,     // expected items count.
                12,     // expected total count.
                30,     // expected items per page.
                1,      // expected last page.
                ElasticsearchFixturesInterface::PREFIX_TEST_INDEX . 'elasticsuite_b2c_fr_product', // expected index alias.
                1.0,    // expected score.
            ],
            [
                'b2c_fr',   // catalog ID.
                5,      // page size.
                2,      // current page.
                [],     // expected error.
                5,      // expected items count.
                12,     // expected total count.
                5,      // expected items per page.
                3,      // expected last page.
                ElasticsearchFixturesInterface::PREFIX_TEST_INDEX . 'elasticsuite_b2c_fr_product', // expected index alias.
                1.0,    // expected score.
            ],
            [
                'b2c_fr',   // catalog ID.
                1000,   // page size.
                null,   // current page.
                [],     // expected error.
                12,     // expected items count.
                12,     // expected total count.
                100,    // expected items per page.
                1,      // expected last page.
                ElasticsearchFixturesInterface::PREFIX_TEST_INDEX . 'elasticsuite_b2c_fr_product', // expected indexalias.
                1.0,    // expected score.
            ],
        ];
    }

    /**
     * @dataProvider sortedSearchProductsProvider
     *
     * @param string $catalogId             Catalog ID or code
     * @param int    $pageSize              Pagination size
     * @param int    $currentPage           Current page
     * @param array  $sortOrders            Sort order specifications
     * @param string $documentIdentifier    Document identifier to check ordered results
     * @param array  $expectedOrderedDocIds Expected ordered document identifiers
     */
    public function testSortedSearchProducts(
        string $catalogId,
        int $pageSize,
        int $currentPage,
        array $sortOrders,
        string $documentIdentifier,
        array $expectedOrderedDocIds
    ): void {
        $user = $this->getUser(Role::ROLE_CONTRIBUTOR);

        $arguments = sprintf(
            'catalogId: "%s", pageSize: %d, currentPage: %d',
            $catalogId,
            $pageSize,
            $currentPage
        );

        if (!empty($sortOrders)) {
            $sortArguments = [];
            foreach ($sortOrders as $field => $direction) {
                $sortArguments[] = sprintf('%s: %s', $field, $direction);
            }
            $arguments .= sprintf(', sort: {%s}', implode(', ', $sortArguments));
        }

        $this->validateApiCall(
            new RequestGraphQlToTest(
                <<<GQL
                    {
                        searchProducts({$arguments}) {
                            collection {
                              id
                              score
                              source
                            }
                            paginationInfo {
                              itemsPerPage
                            }
                        }
                    }
                GQL,
                $user
            ),
            new ExpectedResponse(
                200,
                function (ResponseInterface $response) use (
                    $pageSize,
                    $documentIdentifier,
                    $expectedOrderedDocIds
                ) {
                    $this->assertJsonContains([
                        'data' => [
                            'searchProducts' => [
                                'paginationInfo' => [
                                    'itemsPerPage' => $pageSize,
                                ],
                            ],
                        ],
                    ]);

                    $responseData = $response->toArray();
                    $this->assertIsArray($responseData['data']['searchProducts']['collection']);
                    $this->assertCount(\count($expectedOrderedDocIds), $responseData['data']['searchProducts']['collection']);
                    foreach ($responseData['data']['searchProducts']['collection'] as $index => $document) {
                        /*
                        $this->assertArrayHasKey('score', $document);
                        $this->assertEquals($expectedScore, $document['score']);
                        */
                        $this->assertArrayHasKey('id', $document);
                        $this->assertEquals("/products/{$expectedOrderedDocIds[$index]}", $document['id']);

                        $this->assertArrayHasKey('source', $document);
                        if (\array_key_exists($documentIdentifier, $document['source'])) {
                            $this->assertEquals($expectedOrderedDocIds[$index], $document['source'][$documentIdentifier]);
                        }
                    }
                }
            )
        );
    }

    public function sortedSearchProductsProvider(): array
    {
        return [
            [
                'b2c_en',   // catalog ID.
                10,     // page size.
                1,      // current page.
                [],     // sort order specifications.
                'entity_id', // document data identifier.
                // score DESC first, then id DESC but field 'id' is not present, so missing _first
                // which means the document will be sorted as they were imported.
                // the document.id matched here is the document._id which is entity_id (see fixtures import)
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],    // expected ordered document IDs
            ],
            [
                'b2c_fr',   // catalog ID.
                10,     // page size.
                1,      // current page.
                [],     // sort order specifications.
                'id', // document data identifier.
                // score DESC first, then id DESC which exists in 'b2c_fr'
                // but id DESC w/missing _first, so doc w/entity_id="1" is first
                [1, 12, 11, 10, 9, 8, 7, 6, 5, 4],    // expected ordered document IDs
            ],
            [
                'b2c_fr',   // catalog ID.
                10,     // page size.
                1,      // current page.
                ['id' => SortOrderInterface::SORT_ASC], // sort order specifications.
                'id', // document data identifier.
                // id ASC (missing _last), then score DESC (but not for first doc w/ entity_id="1")
                [2, 3, 4, 5, 6, 7, 8, 9, 10, 11],    // expected ordered document IDs
            ],
            [
                'b2c_fr',   // catalog ID.
                10,     // page size.
                1,      // current page.
                ['size' => SortOrderInterface::SORT_ASC], // sort order specifications.
                'id', // document data identifier.
                // size ASC, then score DESC first, then id DESC (missing _first)
                [10, 5, 11, 2, 4, 3, 6, 9, 7, 1],   // expected ordered document IDs
            ],
            [
                'b2c_fr',   // catalog ID.
                10,     // page size.
                1,      // current page.
                ['size' => SortOrderInterface::SORT_DESC], // sort order specifications.
                'id', // document data identifier.
                // size DESC, then score ASC first, then id ASC (missing _last)
                [8, 12, 1, 7, 9, 6, 3, 4, 2, 11],   // expected ordered document IDs
            ],
            [
                'b2c_fr',   // catalog ID.
                10,     // page size.
                1,      // current page.
                ['created_at' => SortOrderInterface::SORT_ASC], // sort order specifications.
                'id', // document data identifier.
                // size DESC, then score ASC first, then id ASC (missing _last)
                [1, 12, 11, 8, 7, 6, 4, 3, 2, 5],   // expected ordered document IDs
            ],
            [
                'b2c_fr',   // catalog ID.
                5,     // page size.
                1,      // current page.
                ['price_as_nested__price' => SortOrderInterface::SORT_ASC], // sort order specifications.
                'id', // document data identifier.
                // price_as_nested.price ASC, then score DESC first, then id DESC (missing _first)
                [2, 1, 3, 12, 11],   // expected ordered document IDs
            ],
        ];
    }

    public function testSortedSearchProductsInvalidField(): void
    {
        $this->validateApiCall(
            new RequestGraphQlToTest(
                <<<GQL
                    {
                        searchProducts(catalogId: "b2c_fr", sort: { length: desc }) {
                            collection { id }
                        }
                    }
                GQL,
                $this->getUser(Role::ROLE_CONTRIBUTOR)
            ),
            new ExpectedResponse(
                200,
                function (ResponseInterface $response) {
                    $this->assertJsonContains([
                        'errors' => [['message' => 'Field "length" is not defined by type ProductSortInput.']],
                    ]);
                }
            )
        );

        $this->validateApiCall(
            new RequestGraphQlToTest(
                <<<GQL
                    {
                        searchProducts(catalogId: "b2c_fr", sort: { stock__qty: desc }) {
                            collection { id }
                        }
                    }
                GQL,
                $this->getUser(Role::ROLE_CONTRIBUTOR)
            ),
            new ExpectedResponse(
                200,
                function (ResponseInterface $response) {
                    $this->assertJsonContains([
                        'errors' => [['message' => 'Field "stock__qty" is not defined by type ProductSortInput.']],
                    ]);
                }
            )
        );

        $this->validateApiCall(
            new RequestGraphQlToTest(
                <<<GQL
                    {
                        searchProducts(catalogId: "b2c_fr", sort: { price__price: desc }) {
                            collection { id }
                        }
                    }
                GQL,
                $this->getUser(Role::ROLE_CONTRIBUTOR)
            ),
            new ExpectedResponse(
                200,
                function (ResponseInterface $response) {
                    $this->assertJsonContains([
                        'errors' => [['message' => 'Field "price__price" is not defined by type ProductSortInput.']],
                    ]);
                }
            )
        );

        $this->validateApiCall(
            new RequestGraphQlToTest(
                <<<GQL
                    {
                        searchProducts(catalogId: "b2c_fr", sort: { stock_as_nested__qty: desc }) {
                            collection { id }
                        }
                    }
                GQL,
                $this->getUser(Role::ROLE_CONTRIBUTOR)
            ),
            new ExpectedResponse(
                200,
                function (ResponseInterface $response) {
                    $this->assertJsonContains([
                        'errors' => [['message' => 'Field "stock_as_nested__qty" is not defined by type ProductSortInput; Did you mean price_as_nested__price?']],
                    ]);
                }
            )
        );

        $this->validateApiCall(
            new RequestGraphQlToTest(
                <<<GQL
                    {
                        searchProducts(catalogId: "b2c_fr", sort: { id: desc, size: asc }) {
                            collection { id }
                        }
                    }
                GQL,
                $this->getUser(Role::ROLE_CONTRIBUTOR)
            ),
            new ExpectedResponse(
                200,
                function (ResponseInterface $response) {
                    $this->assertJsonContains([
                        'errors' => [['debugMessage' => 'Sort argument : You can\'t sort on multiple attribute.']],
                    ]);
                }
            )
        );
    }

    /**
     * @dataProvider fulltextSearchProductsProvider
     *
     * @param string $catalogId             Catalog ID or code
     * @param int    $pageSize              Pagination size
     * @param int    $currentPage           Current page
     * @param string $searchQuery           Search query
     * @param string $documentIdentifier    Document identifier to check ordered results
     * @param array  $expectedOrderedDocIds Expected ordered document identifiers
     */
    public function testFulltextSearchProducts(
        string $catalogId,
        int $pageSize,
        int $currentPage,
        string $searchQuery,
        string $documentIdentifier,
        array $expectedOrderedDocIds
    ): void {
        $user = $this->getUser(Role::ROLE_CONTRIBUTOR);

        $arguments = sprintf(
            'catalogId: "%s", pageSize: %d, currentPage: %d, search: "%s"',
            $catalogId,
            $pageSize,
            $currentPage,
            $searchQuery,
        );

        $this->validateApiCall(
            new RequestGraphQlToTest(
                <<<GQL
                    {
                        searchProducts({$arguments}) {
                            collection { id score source }
                        }
                    }
                GQL,
                $user
            ),
            new ExpectedResponse(
                200,
                function (ResponseInterface $response) use (
                    $documentIdentifier,
                    $expectedOrderedDocIds
                ) {
                    $responseData = $response->toArray();
                    $this->assertIsArray($responseData['data']['searchProducts']['collection']);
                    $this->assertCount(\count($expectedOrderedDocIds), $responseData['data']['searchProducts']['collection']);
                    foreach ($responseData['data']['searchProducts']['collection'] as $index => $document) {
                        $this->assertArrayHasKey('id', $document);
                        $this->assertEquals("/products/{$expectedOrderedDocIds[$index]}", $document['id']);

                        $this->assertArrayHasKey('source', $document);
                        if (\array_key_exists($documentIdentifier, $document['source'])) {
                            $this->assertEquals($expectedOrderedDocIds[$index], $document['source'][$documentIdentifier]);
                        }
                    }
                }
            )
        );
    }

    public function fulltextSearchProductsProvider(): array
    {
        return [
            [
                'b2c_en',   // catalog ID.
                10,         // page size.
                1,          // current page.
                'striveshoulder', // query.
                'id',       // document data identifier.
                [2],        // expected ordered document IDs
            ],
            [
                'b2c_en',   // catalog ID.
                10,         // page size.
                1,          // current page.
                'bag',      // query.
                'id',       // document data identifier.
                [4, 1, 8, 14, 5, 2, 3],  // expected ordered document IDs
            ],
            [
                'b2c_fr',   // catalog ID.
                10,         // page size.
                1,          // current page.
                'bag',      // query.
                'id',       // document data identifier.
                [],  // expected ordered document IDs
            ],
            [
                'b2c_en',   // catalog ID.
                10,         // page size.
                1,          // current page.
                'summer',   // query: search in description field.
                'id',       // document data identifier.
                [5, 3],  // expected ordered document IDs
            ],
            [
                'b2c_en',   // catalog ID.
                10,         // page size.
                1,          // current page.
                'yoga',      // query: search in multiple field.
                'id',       // document data identifier.
                [8, 3],  // expected ordered document IDs
            ],
            [
                'b2c_en',   // catalog ID.
                10,         // page size.
                1,          // current page.
                'bag autumn', // query: search with multiple words.
                'id',       // document data identifier.
                [4],  // expected ordered document IDs
            ],
            [
                'b2c_en',   // catalog ID.
                10,         // page size.
                1,          // current page.
                'bag automn', // query: search with misspelled word.
                'id',       // document data identifier.
                [4],  // expected ordered document IDs
            ],
            [
                'b2c_en',   // catalog ID.
                10,         // page size.
                1,          // current page.
                'bohqpaq',  // query: search with word with same phonetic.
                'id',       // document data identifier.
                [6, 12, 11, 3],  // expected ordered document IDs
            ],
        ];
    }

    /**
     * @dataProvider filteredSearchDocumentsValidationProvider
     *
     * @param string $catalogId    Catalog ID or code
     * @param string $filter       Filters to apply
     * @param array  $debugMessage Expected debug message
     */
    public function testFilteredSearchProductsGraphQlValidation(
        string $catalogId,
        string $filter,
        array $debugMessage
    ): void {
        $user = $this->getUser(Role::ROLE_CONTRIBUTOR);
        $arguments = sprintf('catalogId: "%s", filter: {%s}', $catalogId, $filter);
        $this->validateApiCall(
            new RequestGraphQlToTest(
                <<<GQL
                    {
                        searchProducts({$arguments}) {
                            collection { id }
                        }
                    }
                GQL,
                $user
            ),
            new ExpectedResponse(
                200,
                function (ResponseInterface $response) use (
                    $debugMessage
                ) {
                    $this->assertJsonContains(['errors' => [$debugMessage]]);
                }
            )
        );
    }

    public function filteredSearchDocumentsValidationProvider(): array
    {
        return [
            [
                'b2c_en', // catalog ID.
                'fake_source_field_match: { match:"sacs" }', // Filters.
                [ // debug message
                    'message' => 'Field "fake_source_field_match" is not defined by type ProductFieldFilterInput.',
                ],
            ],
            [
                'b2c_en', // catalog ID.
                'size: { match: "id" }', // Filters.
                [ // debug message
                    'message' => 'Field "match" is not defined by type ProductIntegerTypeFilterInput.',
                ],
            ],
            [
                'b2c_en', // catalog ID.
                'name: { in: ["Test"], eq: "Test" }', // Filters.
                [ // debug message
                    'debugMessage' => 'Filter argument name: Only \'eq\', \'in\', \'match\' or \'exist\' should be filled.',
                ],
            ],
            [
                'b2c_en', // catalog ID.
                'created_at: { gt: "2022-09-23", gte: "2022-09-23" }', // Filters.
                [ // debug message
                    'debugMessage' => 'Filter argument created_at: Do not use \'gt\' and \'gte\' in the same filter.',
                ],
            ],
        ];
    }

    /**
     * @dataProvider filteredSearchDocumentsProvider
     *
     * @param string $catalogId             Catalog ID or code
     * @param string $filter                Filters to apply
     * @param array  $sortOrders            Sort order specifications
     * @param string $documentIdentifier    Document identifier to check ordered results
     * @param array  $expectedOrderedDocIds Expected ordered document identifiers
     */
    public function testFilteredSearchProducts(
        string $catalogId,
        array $sortOrders,
        string $filter,
        string $documentIdentifier,
        array $expectedOrderedDocIds
    ): void {
        $user = $this->getUser(Role::ROLE_CONTRIBUTOR);
        $arguments = sprintf(
            'catalogId: "%s", pageSize: %d, currentPage: %d, filter: {%s}',
            $catalogId,
            10,
            1,
            $filter
        );

        $this->addSortOrders($sortOrders, $arguments);

        $this->validateApiCall(
            new RequestGraphQlToTest(
                <<<GQL
                    {
                        searchProducts({$arguments}) {
                            collection { id source }
                        }
                    }
                GQL,
                $user
            ),
            new ExpectedResponse(
                200,
                function (ResponseInterface $response) use (
                    $documentIdentifier,
                    $expectedOrderedDocIds
                ) {
                    // Extra test on response structure because all exceptions might not throw an HTTP error code.
                    $this->assertJsonContains([
                        'data' => [
                            'searchProducts' => [
                                'collection' => [],
                            ],
                        ],
                    ]);

                    $responseData = $response->toArray();
                    $this->assertIsArray($responseData['data']['searchProducts']['collection']);
                    $this->assertCount(\count($expectedOrderedDocIds), $responseData['data']['searchProducts']['collection']);
                    foreach ($responseData['data']['searchProducts']['collection'] as $index => $document) {
                        $this->assertArrayHasKey('id', $document);
                        $this->assertEquals("/products/{$expectedOrderedDocIds[$index]}", $document['id']);

                        $this->assertArrayHasKey('source', $document);
                        if (\array_key_exists($documentIdentifier, $document['source'])) {
                            $this->assertEquals($expectedOrderedDocIds[$index], $document['source'][$documentIdentifier]);
                        }
                    }
                }
            )
        );
    }

    public function filteredSearchDocumentsProvider(): array
    {
        return [
            [
                'b2c_fr', // catalog ID.
                [], // sort order specifications.
                'sku: { eq: "24-MB03" }',
                'entity_id', // document data identifier.
                [3], // expected ordered document IDs
            ],
            [
                'b2c_fr', // catalog ID.
                [], // sort order specifications.
                'category__id: { eq: "one" }',
                'entity_id', // document data identifier.
                [1, 2], // expected ordered document IDs
            ],
            [
                'b2c_fr', // catalog ID.
                [], // sort order specifications.
                'created_at: { gte: "2022-09-01", lte: "2022-09-05" }',
                'entity_id', // document data identifier.
                [1, 12, 11, 8, 7, 6, 4, 3, 2], // expected ordered document IDs
            ],
            [
                'b2c_fr', // catalog ID.
                ['id' => SortOrderInterface::SORT_ASC], // sort order specifications.
                'sku: { in: ["24-MB02", "24-WB01"] }', // filter.
                'entity_id', // document data identifier.
                [6, 8], // expected ordered document IDs
            ],
            [
                'b2c_fr', // catalog ID.
                ['id' => SortOrderInterface::SORT_ASC], // sort order specifications.
                'id: { gte: 10, lte: 12 }', // filter.
                'entity_id', // document data identifier.
                [10, 11, 12], // expected ordered document IDs
            ],
            [
                'b2c_fr', // catalog ID.
                ['id' => SortOrderInterface::SORT_ASC], // sort order specifications.
                'id: { gt: 10, lt: 12 }', // filter.
                'entity_id', // document data identifier.
                [11], // expected ordered document IDs
            ],
            [
                'b2c_fr', // catalog ID.
                ['id' => SortOrderInterface::SORT_ASC], // sort order specifications.
                'name: { match: "Compete Track" }', // filter.
                'entity_id', // document data identifier.
                [9], // expected ordered document IDs
            ],
            [
                'b2c_fr', // catalog ID.
                ['id' => SortOrderInterface::SORT_ASC], // sort order specifications.
                'size: { exist: true }', // filter.
                'entity_id', // document data identifier.
                [2, 3, 4, 5, 6, 7, 9, 10, 11, 12], // expected ordered document IDs
            ],
            [
                'b2c_fr', // catalog ID.
                ['id' => SortOrderInterface::SORT_ASC], // sort order specifications.
                'size: { exist: false }', // filter.
                'entity_id', // document data identifier.
                [8], // expected ordered document IDs
            ],
            [
                'b2c_fr', // catalog ID.
                ['id' => SortOrderInterface::SORT_ASC], // sort order specifications.
                <<<GQL
                  name: { match: "Sac" }
                  sku: { in: ["24-WB06", "24-WB03"] }
                GQL, // filter.
                'entity_id', // document data identifier.
                [11, 12], // expected ordered document IDs
            ],
            [
                'b2c_fr', // catalog ID.
                ['id' => SortOrderInterface::SORT_ASC], // sort order specifications.
                <<<GQL
                  boolFilter: {
                    _must: [
                      { name: { match:"Sac" }}
                      { sku: { in: ["24-WB06", "24-WB03"] }}
                    ]
                   }
                GQL, // filter.
                'entity_id', // document data identifier.
                [11, 12], // expected ordered document IDs
            ],
            [
                'b2c_fr', // catalog ID.
                ['id' => SortOrderInterface::SORT_ASC], // sort order specifications.
                <<<GQL
                  boolFilter: {
                    _should: [
                      { sku: { eq: "24-MB05" }}
                      { sku: { eq: "24-UB02" }}
                    ]
                   }
                GQL, // filter.
                'entity_id', // document data identifier.
                [4, 7], // expected ordered document IDs
            ],
            [
                'b2c_fr', // catalog ID.
                ['id' => SortOrderInterface::SORT_ASC], // sort order specifications.
                <<<GQL
                  boolFilter: {
                    _not: [
                      { name: { match:"Sac" }}
                    ]
                  }
                GQL, // filter.
                'entity_id', // document data identifier.
                [5, 9, 10], // expected ordered document IDs
            ],
            [
                'b2c_fr', // catalog ID.
                ['id' => SortOrderInterface::SORT_ASC], // sort order specifications.
                <<<GQL
                  boolFilter: {
                    _must: [
                      {name: {match:"Sac"}}
                    ]
                    _should: [
                      {sku: {eq: "24-WB06"}}
                      {sku: {eq: "24-WB03"}}
                    ]
                  }
                GQL, // filter.
                'entity_id', // document data identifier.
                [11, 12], // expected ordered document IDs
            ],
            [
                'b2c_fr', // catalog ID.
                ['id' => SortOrderInterface::SORT_ASC], // sort order specifications.
                <<<GQL
                  boolFilter: {
                    _must: [
                      {name: {match:"Sac"}}
                    ]
                    _should: [
                      {sku: {eq: "24-WB01"}}
                      {sku: {eq: "24-WB06"}}
                      {sku: {eq: "24-WB03"}}
                    ]
                    _not: [
                      {id: {eq: 11}}
                    ]
                  }
                GQL, // filter.
                'entity_id', // document data identifier.
                [8, 12], // expected ordered document IDs
            ],
            [
                'b2c_fr', // catalog ID.
                ['id' => SortOrderInterface::SORT_ASC], // sort order specifications.
                <<<GQL
                  boolFilter: {
                    _must: [
                      {name: {match:"Sac"}}
                      {boolFilter: {
                        _should: [
                          {sku: {eq: "24-WB06"}}
                          {sku: {eq: "24-WB03"}}
                        ]}
                      }
                    ]
                  }
                GQL, // filter.
                'entity_id', // document data identifier.
                [11, 12], // expected ordered document IDs
            ],
        ];
    }

    private function addSortOrders(array $sortOrders, string &$arguments): void
    {
        if (!empty($sortOrders)) {
            $sortArguments = [];
            foreach ($sortOrders as $field => $direction) {
                $sortArguments[] = sprintf('%s : %s', $field, $direction);
            }
            $arguments .= sprintf(', sort: {%s}', implode(', ', $sortArguments));
        }
    }
}
