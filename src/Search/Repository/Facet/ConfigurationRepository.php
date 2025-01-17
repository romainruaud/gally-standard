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

namespace Elasticsuite\Search\Repository\Facet;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Elasticsuite\Category\Model\Category;
use Elasticsuite\Entity\Model\Doctrine\QueryBuilder;
use Elasticsuite\Metadata\Model\Metadata;
use Elasticsuite\Metadata\Model\SourceField;
use Elasticsuite\Search\Hydrator\FacetConfigurationHydrator;
use Elasticsuite\Search\Model\Facet;

/*
 * @method Facet\Configuration|null find($id, $lockMode = null, $lockVersion = null)
 * @method Facet\Configuration|null findOneBy(array $criteria, array $orderBy = null)
 * @method Facet\Configuration[]    findAll()
 * @method Facet\Configuration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConfigurationRepository extends ServiceEntityRepository
{
    private ?string $categoryId = null;
    private ?Metadata $metadata = null;
    private ?string $search = null;

    public function __construct(
        ManagerRegistry $registry
    ) {
        parent::__construct($registry, Facet\Configuration::class);
    }

    public function getCategoryId(): ?string
    {
        return $this->categoryId ?: '0';
    }

    public function setCategoryId(?string $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function getMetadata(): ?Metadata
    {
        return $this->metadata;
    }

    public function setMetadata(?Metadata $metadata): void
    {
        $this->metadata = $metadata;
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function setSearch(?string $search): void
    {
        $this->search = $search;
    }

    /**
     * Get facet configuration by source field.
     */
    public function findOndBySourceField(SourceField $sourceField): ?Facet\Configuration
    {
        $query = $this->createQueryBuilder('o')
            ->andWhere('sf = :sourceField')
            ->setParameter('sourceField', $sourceField)
            ->getQuery();

        return $query->getOneOrNullResult($query->getHydrationMode());
    }

    /**
     * Get all facet configuration.
     *
     * @return Facet\Configuration[]
     */
    public function findAll(): array
    {
        $query = $this->createQueryBuilder('o')->getQuery();

        return $query->getResult($query->getHydrationMode());
    }

    public function createQueryBuilder($alias, $indexBy = null): QueryBuilder|\Doctrine\ORM\QueryBuilder
    {
        $category = $this->getCategoryId();

        // Use custom query builder in order to be able to override hydratation mode.
        $queryBuilder = new QueryBuilder($this->getEntityManager());
        $queryBuilder
            ->select([
                "{$alias}",
                'sf.id AS source_field_id',
                "CONCAT(sf.id, '-', :category) AS id",
                "(case when {$alias}.position IS NOT NULL then {$alias}.position else default.position end) AS position",
            ])
            ->from(SourceField::class, 'sf', $indexBy)
            ->leftJoin(Metadata::class, 'metadata', Join::WITH, 'sf.metadata = metadata.id')
            ->where('sf.isFilterable = true')
            ->orderBy('position', 'ASC')
            ->setParameter('category', $category);

        if ($category) {
            $queryBuilder->leftJoin(
                    $this->_entityName,
                    $alias,
                    Join::WITH,
                    "sf.id = {$alias}.sourceField and {$alias}.category = :category"
                )
                ->leftJoin(Category::class, 'category', Join::WITH, "{$alias}.category = category.id")
                ->addSelect("CONCAT('', :category) as category_id");
        } else {
            $queryBuilder->leftJoin(
                $this->_entityName,
                $alias,
                Join::WITH,
                "sf.id = {$alias}.sourceField and {$alias}.category is NULL"
            );
        }

        if ($this->getMetadata()) {
            $queryBuilder->andWhere('metadata.entity = :entity')
                ->setParameter('entity', $this->getMetadata()->getEntity());
        }

        if ($this->getSearch()) {
            $queryBuilder->andWhere('LOWER(sf.search) LIKE LOWER(:search)')
                ->setParameter('search', "%{$this->getSearch()}%");
        }

        $queryBuilder
            ->leftJoin(
                $this->_entityName,
                'default',
                Join::WITH,
                'sf.id = default.sourceField and default.category is NULL'
            )
            ->addSelect('default');

        $this->getEntityManager()
            ->getConfiguration()
            ->addCustomHydrationMode(FacetConfigurationHydrator::CODE, FacetConfigurationHydrator::class);
        $queryBuilder->setHydratationMode(FacetConfigurationHydrator::CODE);

        // Force root query alias as the table in the "from" part of the request
        // is not the table of facet configuration entities.
        $queryBuilder->setForcedRootAliases([$alias]);

        return $queryBuilder;
    }
}
