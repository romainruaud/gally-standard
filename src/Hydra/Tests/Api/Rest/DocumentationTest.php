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

namespace Elasticsuite\Hydra\Tests\Api\Rest;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class DocumentationTest extends ApiTestCase
{
    public function testAdditionalHydraDocumentation(): void
    {
        $response = static::createClient()->request('GET', '/docs.jsonld');
        $this->assertResponseIsSuccessful();

        $response = json_decode($response->getContent(), true);

        $classKey = array_search('SourceField', array_column($response['hydra:supportedClass'] ?? [], 'hydra:title'), true);
        $this->assertNotFalse($classKey, "Class 'SourceField' is missing in 'hydra:supportedClass' node in docs.jsonld.");

        $propertyTypeKey = array_search('type', array_column($response['hydra:supportedClass'][$classKey]['hydra:supportedProperty'] ?? [], 'hydra:title'), true);
        $this->assertNotFalse($propertyTypeKey, "Property 'type' for the class 'SourceField' is missing in docs.jsonld.");

        // The goal of this test, is to check that the documentation added on ApiResource properties metadata are added in docs.jsonld
        static::assertArraySubset(
            [
                'hydra:title' => 'type',
                'hydra:property' => [
                    '@id' => '#SourceField/type',
                    'rdfs:label' => 'Attribute type',
                ],
                'elasticsuite' => [
                    'visible' => true,
                    'editable' => false,
                    'position' => 30,
                    'context' => [
                        'search_configuration_attributes' => [
                            'visible' => false,
                        ],
                    ],
                ],
            ],
            $response['hydra:supportedClass'][$classKey]['hydra:supportedProperty'][$propertyTypeKey]
        );
    }
}
