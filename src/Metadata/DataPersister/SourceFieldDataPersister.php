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

namespace Gally\Metadata\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use Doctrine\ORM\EntityManagerInterface;
use Gally\Metadata\Model\SourceField;

class SourceFieldDataPersister implements DataPersisterInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data): bool
    {
        return $data instanceof SourceField;
    }

    /**
     * {@inheritdoc}
     *
     * @param SourceField $data
     *
     * @return SourceField
     */
    public function persist($data)
    {
        $sourceField = $data;

        // Is it an update ?
        if ($this->entityManager->getUnitOfWork()->isInIdentityMap($sourceField)) {
            // Call function computeChangeSets to get the entity changes from the function getEntityChangeSet.
            $this->entityManager->getUnitOfWork()->computeChangeSets();
            $changeSet = $this->entityManager->getUnitOfWork()->getEntityChangeSet($sourceField);

            unset($changeSet['isSpellchecked']);
            unset($changeSet['weight']);

            // Prevent user to update a system source field, only the value of 'weight' and 'isSpellchecked' can be changed.
            if (\count($changeSet) > 0 && ($sourceField->getIsSystem() || ($changeSet['isSystem'][0] ?? false) === true)) {
                throw new InvalidArgumentException(sprintf("The source field '%s' cannot be updated because it is a system source field, only the value  of 'weight' and 'isSpellchecked' can be changed.", $sourceField->getCode()));
            }
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }

    /**
     * {@inheritdoc}
     *
     * @param SourceField $data
     */
    public function remove($data)
    {
        // Prevent user to delete system source fields
        if ($data->getIsSystem()) {
            throw new InvalidArgumentException('You can`t remove system source field');
        }

        $this->entityManager->remove($data);
        $this->entityManager->flush();
    }
}
